<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\Document\DispatchDocument;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\DocumentGeneratorAwareInterface as DocGenAwareInterface;
use Dvsa\Olcs\Api\Domain\DocumentGeneratorAwareTrait;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Process ContinuationDetail
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Process extends AbstractCommandHandler implements TransactionedInterface, DocGenAwareInterface
{
    use DocumentGeneratorAwareTrait;

    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['Document', 'FeeType', 'Fee'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $continuationDetail = $this->getRepo()->fetchUsingId($command);

        if ($continuationDetail->getStatus()->getId() !== ContinuationDetailEntity::STATUS_PRINTING) {
            $result
                ->addId('continuationDetail', $continuationDetail->getId())
                ->addMessage('Continuation detail no longer pending');
            return $result;
        }

        // 1. Generate the checklist document
        $result->merge($this->generateDocument($continuationDetail));

        // 2. Update continuation detail record with the checklist document
        // reference and 'printed' status
        $document = $this->getRepo('Document')->fetchById($result->getId('document'));
        $status = $this->getRepo()->getRefdataReference(ContinuationDetailEntity::STATUS_PRINTED);
        $continuationDetail
            ->setChecklistDocument($document)
            ->setStatus($status);
        $this->getRepo()->save($continuationDetail);
        $result
            ->addId('continuationDetail', $continuationDetail->getId())
            ->addMessage('ContinuationDetail updated');

        // 3. Create the continuation fee, if applicable
        $result->merge($this->createFee($continuationDetail));

        return $result;
    }

    /**
     * @param ContinuationDetailEntity $continuationDetail
     * @return Result
     */
    protected function generateDocument(ContinuationDetailEntity $continuationDetail)
    {
        $template = $this->getTemplateName($continuationDetail);

        $storedFile = $this->generateChecklist($continuationDetail, $template);

        $data = [
            'identifier' => $storedFile->getIdentifier(),
            'size' => $storedFile->getSize(),
            'description' => 'Continuation checklist',
            'filename' => $template . '.rtf',
            'licence' => $continuationDetail->getLicence()->getId(),
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isReadOnly'  => 'Y',
            'isExternal'  => false,
            'isScan' => false,
            // of the three boolean flags, only isReadOnly is mapped as YesNoNull :-/
        ];

        return $this->handleSideEffect(DispatchDocument::create($data));
    }

    protected function getTemplateName($continuationDetail)
    {
        $licence = $continuationDetail->getLicence();

        $template = $licence->isGoods() ? 'GV' : 'PSV';

        if ($licence->getLicenceType()->getId() === LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $template .= 'SR';
        }

        $template .= 'Checklist';

        return $template;
    }

    /**
     * @param ContinuationDetailEntity $continuationDetail
     * @param string $template template name
     */
    protected function generateChecklist($continuationDetail, $template)
    {
        $licence = $continuationDetail->getLicence();
        $query = [
            'licence' => $licence->getId(),
            'goodsOrPsv' => $licence->getGoodsOrPsv()->getId(),
            'licenceType' => $licence->getLicenceType()->getId(),
            'niFlag' => $licence->getNiFlag(),
            'organisation' => $licence->getOrganisation()->getId(),
        ];

        $storedFile = $this->getDocumentGenerator()->generateAndStore($template, $query);

        return $storedFile;
    }


    /**
     * @param ContinuationDetailEntity $continuationDetail
     * @return Result
     */
    protected function createFee(ContinuationDetailEntity $continuationDetail)
    {
        $result = new Result();

        $licence = $continuationDetail->getLicence();

        if ($this->shouldCreateFee($licence)) {

            $now = new DateTime();

            $feeType = $this->getRepo('FeeType')->fetchLatest(
                $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_CONT),
                $licence->getGoodsOrPsv(),
                $licence->getLicenceType(),
                $now,
                $licence->getTrafficArea()
            );

            $amount = ($feeType->getFixedValue() != 0 ? $feeType->getFixedValue() : $feeType->getFiveYearValue());

            $data = [
                'feeType' => $feeType->getId(),
                'amount' => $amount,
                'invoicedDate' => $now->format('Y-m-d'),
                'licence' => $licence->getId(),
                'description' => $feeType->getDescription() . ' for licence ' . $licence->getLicNo(),
            ];

            $result = $this->handleSideEffect(CreateFee::create($data));
        }

        return $result;
    }

    /**
     * We want to create a fee if the licence type is goods, or psv special restricted
     * and there is no existing CONT fee
     *
     * @param LicenceEntity $licence
     * @return boolean
     */
    protected function shouldCreateFee(LicenceEntity $licence)
    {
        // If PSV and not SR then we don't need to create a fee
        if ($licence->isPsv()
            && $licence->getLicenceType()->getId() !== LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return false;
        }

        $results = $this->getRepo('Fee')->fetchOutstandingContinuationFeesByLicenceId($licence->getId());

        return empty($results);
    }
}
