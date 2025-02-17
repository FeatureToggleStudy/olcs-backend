<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\DeleteSubmission as DeleteEbsrSubmission;
use Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox;
use Dvsa\Olcs\Api\Domain\Repository\SlaTargetDate;

/**
 * Delete Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteDocument extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Document';

    protected $extraRepos = ['CorrespondenceInbox','SlaTargetDate'];

    /**
     * @var ContentStoreFileUploader
     */
    private $fileUploader;

    /**
     * Creates service (needs instance of file uploader)
     *
     * @param ServiceLocatorInterface $serviceLocator service locator
     *
     * @return TransactioningCommandHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceLocator  */
        $mainServiceLocator = $serviceLocator->getServiceLocator();

        $this->fileUploader = $mainServiceLocator->get('FileUploader');

        return parent::createService($serviceLocator);
    }

    /**
     * Deletes a document and optionally triggers side effect of deleting the associated EBSR submission
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Document $document */
        $document = $this->getRepo()->fetchUsingId($command);
        $identifier = $document->getIdentifier();

        if (!empty($identifier)) {
            $this->fileUploader->remove($identifier);
            $result->addMessage('File removed');
        }

        //if it's an EBSR doc, also delete the associated submission
        if ($document->getEbsrSubmission()) {
            $result->merge(
                $this->handleSideEffect(
                    DeleteEbsrSubmission::create(['id' => $document->getEbsrSubmission()->getId()])
                )
            );
        }

        /** @var CorrespondenceInbox $correspondenceInboxeRepo */
        $correspondenceInboxeRepo = $this->getRepo('CorrespondenceInbox');
        $correspondenceInboxes = $correspondenceInboxeRepo->fetchByDocumentId($document->getId());
        foreach ($correspondenceInboxes as $correspondenceInbox) {
            $this->getRepo('CorrespondenceInbox')->delete($correspondenceInbox);
        }

        /** @var SlaTargetDate $slaTargetDateRepo */
        $slaTargetDateRepo = $this->getRepo('SlaTargetDate');
        $slaTargetDates = $slaTargetDateRepo->fetchByDocumentId($document->getId());
        foreach ($slaTargetDates as $slaTargetDate) {
            $this->getRepo('SlaTargetDate')->delete($slaTargetDate);
        }

        $this->getRepo()->delete($document);

        $result->addMessage('Document deleted');

        return $result;
    }
}
