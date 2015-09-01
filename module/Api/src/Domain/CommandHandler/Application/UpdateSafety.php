<?php

/**
 * Update Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateSafety as LicenceUpdateSafety;

/**
 * Update Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateSafety extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        if ($command->getPartial() === false && $command->getSafetyConfirmation() !== 'Y') {
            throw new ValidationException(
                [
                    'safetyConfirmation' => [
                        [
                            Application::ERROR_SAFE_REQUIRE_CONFIRMATION
                                => 'You must confirm the safety arrangements are suitable'
                        ]
                    ]
                ]
            );
        }

        $application->setSafetyConfirmation($command->getSafetyConfirmation());

        $this->getRepo()->save($application);

        $result->addMessage('Application updated');

        $licenceData = $command->getLicence();

        if ((int)$application->getTotAuthTrailers() < 1) {
            $licenceData['safetyInsTrailers'] = 0;
        }

        $result->merge($this->handleSideEffect(LicenceUpdateSafety::create($licenceData)));

        $data = [
            'id' => $command->getId(),
            'section' => 'safety',
            'data' => [
                'hasChanged' => $application->getVersion() != $command->getVersion()
            ]
        ];

        $result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));

        return $result;
    }
}
