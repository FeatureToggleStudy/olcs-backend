<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Transfer\Command\Permits\QueueAcceptScoring as QueueAcceptScoringCmd;
use Dvsa\Olcs\Api\Domain\Query\Permits\QueueAcceptScoringPermitted as QueueAcceptScoringPermittedQry;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Queue accept scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QueueAcceptScoring extends AbstractCommandHandler implements ToggleRequiredInterface
{
    use QueueAwareTrait, ToggleAwareTrait;

    protected $repoServiceName = 'IrhpPermitStock';

    protected $toggleConfig = [FeatureToggle::BACKEND_ECMT];

    /**
     * Handle command
     *
     * @param QueueAcceptScoringCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $stockId = $command->getId();

        $permittedResult = $this->handleQuery(
            QueueAcceptScoringPermittedQry::create(['id' => $stockId])
        );

        if (!$permittedResult['result']) {
            $this->result->addMessage('Unable to queue accept scoring: ' . $permittedResult['message']);
            return $this->result;
        }

        $stockRepo = $this->getRepo();
        $stock = $stockRepo->fetchById($stockId);
        $stock->proceedToAcceptPending($this->refData(IrhpPermitStock::STATUS_ACCEPT_PENDING));
        $stockRepo->save($stock);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createQueue($stockId, Queue::TYPE_ACCEPT_ECMT_SCORING, [])
            )
        );

        $this->result->addMessage('Queueing accept scoring of ECMT applications');
        return $this->result;
    }
}
