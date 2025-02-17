<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as Entity;
use Dvsa\Olcs\Api\Entity\Permits\EcmtPermitApplication;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Permits\UnpaidEcmtPermits;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRHP Candidate Permit
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpCandidatePermit extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Returns the ids of in scope candidate permits within the specified stock where the randomised score is not empty
     * and the associated application has requested the specified sector, ordered by randomised score descending
     *
     * @param int $stockId
     * @param int $sectorsId
     *
     * @return array
     */
    public function getScoreOrderedIdsBySectorInScope($stockId, $sectorsId)
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('icp.id')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('IDENTITY(epa.sectors) = ?2')
            ->andWhere('epa.inScope = 1')
            ->orderBy('icp.randomizedScore', 'DESC')
            ->setParameter(1, $stockId)
            ->setParameter(2, $sectorsId)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'id');
    }

    /**
     * Returns the count of candidate permits in the specified stock that are marked as successful and where the
     * associated application relates to a licence for the specified jurisdiction/devolved administration
     *
     * @param int $stockId
     * @param int $jurisdictionId
     *
     * @return int
     */
    public function getSuccessfulDaCountInScope($stockId, $jurisdictionId)
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('count(icp.id)')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('epa.licence', 'l')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 1')
            ->andWhere('IDENTITY(l.trafficArea) = ?2')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $stockId)
            ->setParameter(2, $jurisdictionId)
            ->getQuery()
            ->getSingleScalarResult();

        if (is_null($result)) {
            return 0;
        }

        return $result;
    }

    /**
     * Returns the ids of candidate permits within the specified stock that have a randomised score and are marked as
     * unsuccessful, ordered by randomised score descending. Optional parameter to further filter the results by
     * the traffic area of the associated application
     *
     * @param int $stockId
     * @param int $trafficAreaId (optional)
     *
     * @return array
     */
    public function getUnsuccessfulScoreOrderedIdsInScope($stockId, $trafficAreaId = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('icp.id')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 0')
            ->andWhere('epa.inScope = 1')
            ->orderBy('icp.randomizedScore', 'DESC')
            ->setParameter(1, $stockId);

        if (!is_null($trafficAreaId)) {
            $queryBuilder->innerJoin('epa.licence', 'l')
            ->andWhere('IDENTITY(l.trafficArea) = ?2')
            ->setParameter(2, $trafficAreaId);
        }

        $result = $queryBuilder->getQuery()->getScalarResult();
        return array_column($result, 'id');
    }

    /**
     * Returns the count of candidate permits in the specified stock marked as successful
     *
     * @param int $stockId
     *
     * @return int
     */
    public function getSuccessfulCountInScope($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(icp)')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 1')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the candidate permits in the specified stock marked as successful
     *
     * @param int $stockId
     *
     * @return array
     */
    public function getSuccessfulScoreOrderedInScope($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('icp')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 1')
            ->andWhere('epa.inScope = 1')
            ->orderBy('icp.randomizedScore', 'DESC')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Marks a set of candidate permit ids as successful
     *
     * @param array $candidatePermitIds
     */
    public function markAsSuccessful(array $candidatePermitIds)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->update(Entity::class, 'icp')
            ->set('icp.successful', 1)
            ->where('icp.id in (?1)')
            ->setParameter(1, $candidatePermitIds)
            ->getQuery();

        $query->execute();
    }

    /**
     * Retrieves the ids of candidate permits and corresponding licence numbers in scope for the current scoring run
     *
     * @param int $irhpPermitStockId the Id of the IrhpPermitStock that the scoring will be for
     *
     * @return array a list of candidate permit ids and corresponding licence numbers
     */
    public function fetchDeviationSourceValues($irhpPermitStockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('icp.id as candidatePermitId, l.licNo, epa.id as applicationId, epa.permitsRequired')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->innerJoin('epa.licence', 'l')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $irhpPermitStockId)
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Apply List Filters
     *
     * @param QueryBuilder   $qb    Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query instanceof UnpaidEcmtPermits) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.successful', ':successful'))
                ->setParameter('successful', true);
            $qb->andWhere($qb->expr()->eq('epa.status', ':status'))
                ->setParameter('status', $query->getStatus());
            $qb->andWhere($qb->expr()->eq('ipa.ecmtPermitApplication', ':ecmtId'))
                ->setParameter('ecmtId', $query->getId());
        }
        if (method_exists($query, 'getEcmtPermitApplication')) {
            $qb->andWhere($qb->expr()->eq('epa.id', ':ecmtId'))
                ->setParameter('ecmtId', $query->getEcmtPermitApplication());
        }
    }

    /**
     * Add List Joins
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('irhpPermitApplication', 'ipa')
            ->with('ipa.ecmtPermitApplication', 'epa');
    }

    /**
     * Retrieves a partial list of column values for the scoring report
     *
     * @param int $irhpPermitStockId the Id of the IrhpPermitStock that the scoring will be for
     *
     * @return array
     */
    public function fetchScoringReport($irhpPermitStockId)
    {
        $columns = [
            'icp.id as candidatePermitId',
            'epa.id as applicationId',
            'o.name as organisationName',
            'icp.applicationScore as candidatePermitApplicationScore',
            'icp.intensityOfUse as candidatePermitIntensityOfUse',
            'icp.randomFactor as candidatePermitRandomFactor',
            'icp.randomizedScore as candidatePermitRandomizedScore',
            'IDENTITY(epa.internationalJourneys) as applicationInternationalJourneys',
            's.name as applicationSectorName',
            'l.licNo as licenceNo',
            'ta.id as trafficAreaId',
            'ta.name as trafficAreaName',
            'icp.successful as candidatePermitSuccessful',
            'IDENTITY(icp.irhpPermitRange) as candidatePermitRangeId'
        ];

        return $this->getEntityManager()->createQueryBuilder()
            ->select(implode(', ', $columns))
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.ecmtPermitApplication', 'epa')
            ->innerJoin('epa.licence', 'l')
            ->innerJoin('epa.sectors', 's')
            ->innerJoin('l.trafficArea', 'ta')
            ->innerJoin('l.organisation', 'o')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $irhpPermitStockId)
            ->getQuery()
            ->getScalarResult();
    }
}
