<?php

/**
 * Create Transport Manager Appplication
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TransportManagerApplicationEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\CreateForResponsibilities as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Create Transport Manager Appplication
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateForResponsibilities extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = ['Application'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $this->validateTransportManagerApplication($command);

        $tmApplication = $this->createTransportManagerApplicationObject($command);

        $this->getRepo()->save($tmApplication);

        $result->addId('transportManagerApplication', $tmApplication->getId());
        $result->addMessage('Transport Manager Application created successfully');
        return $result;
    }

    private function validateTransportManagerApplication($command)
    {
        try {
            $application = $this->getRepo('Application')->fetchWithLicence($command->getApplication());
        } catch (\Exception $e) {
            throw new ValidationException(
                [
                    'application' =>  'The application ID is not valid'
                ]
            );
        }
        $licenceType = $application->getLicence()->getLicenceType()->getId();
        if ($licenceType === LicenceEntity::LICENCE_TYPE_RESTRICTED ||
            $licenceType === LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            throw new ValidationException(
                [
                    'application' =>  'A transport manager cannot be added to a restricted licence'
                ]
            );
        }
        $tmApplication = $this->getRepo()
            ->fetchByTmAndApplication($command->getTransportManager(), $command->getApplication());
        if ($tmApplication) {
            throw new ValidationException(
                [
                    'application' =>  'The transport manager is already linked to this application'
                ]
            );
        }
    }

    /**
     * @param Cmd $command
     * @return TransportManagerApplicationEntity
     */
    private function createTransportManagerApplicationObject($command)
    {
        $tmApplication = new TransportManagerApplicationEntity();

        $application = $this->getRepo('Application')
            ->fetchWithTmLicences($command->getApplication());

        $tmApplication->updateTransportManagerApplication(
            $this->getRepo()->getReference(ApplicationEntity::class, $command->getApplication()),
            $this->getRepo()->getReference(TransportManagerEntity::class, $command->getTransportManager()),
            isset($application['licence']['tmLicences']) && count($application['licence']['tmLicences']) ? 'U' : 'A',
            $this->getRepo()->getRefdataReference(TransportManagerApplicationEntity::STATUS_POSTAL_APPLICATION),
            $this->getCurrentUser()
        );
        return $tmApplication;
    }
}
