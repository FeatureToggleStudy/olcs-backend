<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create as Cmd;

/**
 * Create Irhp Permit Application
 */
final class Create extends AbstractCommandHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_ECMT];
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle command
     *
     * @param Cmd $command command
     *
     * @return Result
     * @throws NotFoundException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {

        /** @var IrhpApplicationRepo $irhpApplicationRepo */
        $irhpApplicationRepo = $this->getRepo();
        /** @var IrhpPermitTypeEntity $permitType */
        $permitType = $irhpApplicationRepo->getReference(IrhpPermitTypeEntity::class, $command->getType());

        if (!($permitType instanceof IrhpPermitTypeEntity)) {
            throw new NotFoundException('Permit type not found');
        }

        $irhpApplication = IrhpApplicationEntity::createNew(
            $this->refData(IrhpInterface::SOURCE_SELFSERVE),
            $this->refData(IrhpInterface::STATUS_NOT_YET_SUBMITTED),
            $irhpApplicationRepo->getReference(IrhpPermitTypeEntity::class, $permitType->getId()),
            $irhpApplicationRepo->getReference(LicenceEntity::class, $command->getLicence()),
            date('Y-m-d')
        );

        $irhpApplicationRepo->save($irhpApplication);

        $this->result->addId('irhpApplication', $irhpApplication->getId());
        $this->result->addMessage('IRHP Application created successfully');

        return $this->result;
    }
}
