<?php

/**
 * Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CommonGrant;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs;
use Dvsa\Olcs\Transfer\Command\Licence\VoidPsvDiscs;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;

/**
 * Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Grant extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['GoodsDisc'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);
        $licence = $application->getLicence();

        $result->merge($this->createSnapshot($command->getId()));

        $this->updateStatusAndDate($application, ApplicationEntity::APPLICATION_STATUS_VALID);

        if ($application->getLicenceType() !== $licence->getLicenceType()) {
            $this->updateExistingDiscs($application, $licence, $result);
        }

        $result->merge($this->proxyCommand($command, CreateDiscRecords::class));

        $licence->copyInformationFromApplication($application);

        $result->merge($this->proxyCommand($command, ProcessApplicationOperatingCentres::class));
        $result->merge($this->proxyCommand($command, CommonGrant::class));

        return $result;
    }

    protected function createSnapshot($applicationId)
    {
        $data = [
            'id' => $applicationId,
            'event' => CreateSnapshot::ON_GRANT
        ];

        return $this->handleSideEffect(CreateSnapshot::create($data));
    }

    /**
     * @param ApplicationEntity|Licence $entity
     * @param $status
     */
    protected function updateStatusAndDate($entity, $status)
    {
        $entity->setStatus($this->getRepo()->getRefdataReference($status));
        $entity->setGrantedDate(new DateTime());
    }

    protected function updateExistingDiscs(ApplicationEntity $application, Licence $licence, Result $result)
    {
        if ($application->isGoods()) {
            $this->updateExistingGoodsDiscs($application, $licence, $result);
        } else {
            $this->updateExistingPsvDiscs($licence, $result);
        }
    }

    protected function updateExistingPsvDiscs(Licence $licence, Result $result)
    {
        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->isNull('ceasedDate'));

        $psvDiscs = $licence->getPsvDiscs()->matching($criteria);

        $ids = array_map(
            function ($v) {
                return $v['id'];
            },
            $psvDiscs
        );

        $params = ['licence' => $licence->getId(), 'ids' => $ids];

        $result->merge($this->handleSideEffect(VoidPsvDiscs::create($params)));

        $dtoData = [
            'licence' => $licence->getId(),
            'amount' => count($ids),
            'isCopy' => 'N'
        ];

        $result->merge(
            $this->handleSideEffect(CreatePsvDiscs::create($dtoData))
        );
    }

    protected function updateExistingGoodsDiscs(ApplicationEntity $application, Licence $licence, Result $result)
    {
        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->neq('specifiedDate', null));
        $criteria->andWhere($criteria->expr()->isNull('removalDate'));
        $criteria->andWhere($criteria->expr()->isNull('interimApplication'));
        $criteria->andWhere($criteria->expr()->neq('application', $application));

        $vehicles = $licence->getLicenceVehicles()->matching($criteria);

        $now = new DateTime();

        /** @var LicenceVehicle $vehicle */
        foreach ($vehicles as $vehicle) {
            /** @var GoodsDisc $disc */
            foreach ($vehicle->getGoodsDiscs() as $disc) {
                if ($disc->getCeasedDate() === null) {
                    $disc->setCeasedDate($now);
                }
            }

            $newDisc = new GoodsDisc($vehicle);
            $newDisc->setIsCopy('N');

            $this->getRepo('GoodsDisc')->save($newDisc);
        }

        $result->addMessage($vehicles->count() . ' Goods Disc(s) replaces');
    }
}
