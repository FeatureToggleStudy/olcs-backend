<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;

/**
 * Delete an IRHP Permit Window
 *
 * @author Andy Newton
 */
final class Delete extends AbstractCommandHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::ADMIN_PERMITS];
    protected $repoServiceName = 'IrhpPermitWindow';

    /**
     * Delete Command Handler
     *
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $id = $command->getId();
        $window = $this->getRepo()->fetchById($id);

        if (!$window->canBeDeleted()) {
            throw new ValidationException(['irhp-permit-windows-cannot-delete-past-or-active-windows']);
        }

        try {
            $this->getRepo()->delete($window);
            $this->result->addId('id', $id);
            $this->result->addMessage(sprintf('Permit Window Deleted', $id));
        } catch (NotFoundException $e) {
            $this->result->addMessage(sprintf('Id %d not found', $id));
        }

        return $this->result;
    }
}
