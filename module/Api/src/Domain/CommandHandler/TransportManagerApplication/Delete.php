<?php

/**
 * Delete a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Delete extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $tmaId) {
            /* @var $tma TransportManagerApplication */
            $tma = $this->getRepo()->fetchById($tmaId);
            $this->getRepo()->delete($tma);
            $result->addMessage("Transport Manager Application ID {$tmaId} deleted");
        }

        return $result;
    }
}
