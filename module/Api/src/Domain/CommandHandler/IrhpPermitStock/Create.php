<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitJurisdiction\Create as CreateDevolvedQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitSector\Create as CreateSectorQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as StockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create an IRHP Permit Stock
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface, ToggleRequiredInterface
{
    use ToggleAwareTrait;
    use IrhpPermitStockTrait;

    protected $toggleConfig = [FeatureToggle::ADMIN_PERMITS];
    protected $repoServiceName = 'IrhpPermitStock';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        // This shared method is defined in IrhpPermitStockTrait - and can throw a ValidationException
        $this->duplicateStockCheck($command);

        $irhpPermitType = $this->getRepo('IrhpPermitStock')->getReference(IrhpPermitTypeEntity::class, $command->getIrhpPermitType());
        $country = null;
        if ($command->getIrhpPermitType() === IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL) {
            $country = $this->getRepo('IrhpPermitStock')->getReference(Country::class, $command->getCountry());
        }

        $stock = StockEntity::create(
            $irhpPermitType,
            $country,
            $command->getValidFrom(),
            $command->getValidTo(),
            $command->getInitialStock(),
            $this->getRepo()->getRefDataReference(StockEntity::STATUS_SCORING_NEVER_RUN)
        );

        try {
            $this->getRepo('IrhpPermitStock')->save($stock);
        } catch (\Exception $e) {
            throw new ValidationException(['You cannot create a duplicate stock']);
        }

        $stockId = $stock->getId();

        $sideEffects = [
            CreateSectorQuotasCmd::create(['id' => $stockId]),
            CreateDevolvedQuotasCmd::create(['id' => $stockId])
        ];

        $this->result->merge(
            $this->handleSideEffects($sideEffects)
        );

        $this->result->addId('IrhpPermitStock', $stockId);
        $this->result->addMessage("IRHP Permit Stock '{$stockId}' created");

        return $this->result;
    }
}
