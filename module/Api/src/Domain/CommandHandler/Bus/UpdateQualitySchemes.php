<?php

/**
 * Update Quality Schemes
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateQualitySchemes as UpdateQualityCmd;

/**
 * Update Quality Schemes
 */
final class UpdateQualitySchemes extends AbstractCommandHandler
{
    protected $repoServiceName = 'Bus';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateQualityCmd $command */
        /** @var BusReg $busReg */

        $result = new Result();

        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $busReg->updateQualitySchemes(
            $command->getIsQualityPartnership(),
            $command->getQualityPartnershipDetails(),
            $command->getQualityPartnershipFacilitiesUsed(),
            $command->getIsQualityContract(),
            $command->getQualityContractDetails()
        );

        try {
            $this->getRepo()->save($busReg);
            $result->addMessage('Saved successfully');
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
