<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as Entity;

/**
 * IRHP Permit Range
 */
class IrhpPermitRange extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Returns the number of possible permit numbers across all ranges in the specified stockId. Will return NULL if
     * no ranges were found against the specified stockId
     *
     * @param int $stockId
     *
     * @return int|null
     */
    public function getCombinedRangeSize($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('SUM((ipr.toNo - ipr.fromNo) + 1)')
            ->from(Entity::class, 'ipr')
            ->where('ipr.ssReserve = false')
            ->andWhere('ipr.lostReplacement = false')
            ->andWhere('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /*
     * Fetch Ranges by Permit Stock ID
     *
     * @param int $irhpPermitStockId
     * @return array
     */
    public function fetchByIrhpPermitStockId($irhpPermitStockId)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irhpPermitStock', ':irhpPermitStock'))
            ->setParameter('irhpPermitStock', $irhpPermitStockId);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch Overlapping Ranges by Permit Stock ID, proposed Start value and proposed End value
     *
     * @param IrhpPermitStock $irhpPermitStock
     * @param int $proposedStartValue
     * @param int $proposedEndValue
     * @param null $irhpPermitRange
     * @return array
     */
    public function findOverlappingRangesByType($irhpPermitStock, $proposedStartValue, $proposedEndValue, $irhpPermitRange = null)
    {
        $doctrineQb = $this->createQueryBuilder();
        $doctrineQb
            ->orWhere($doctrineQb->expr()->between($this->alias . '.fromNo', ':fromNo', ':toNo'))
            ->orWhere($doctrineQb->expr()->between($this->alias . '.toNo', ':fromNo', ':toNo'))
            ->orWhere($doctrineQb->expr()->between(':fromNo', $this->alias . '.fromNo', $this->alias .'.toNo'))
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irhpPermitStock', ':irhpPermitStock'))
            ->setParameter('irhpPermitStock', $irhpPermitStock)
            ->setParameter('fromNo', $proposedStartValue)
            ->setParameter('toNo', $proposedEndValue);

        if ($irhpPermitRange !== null) {
            $doctrineQb
                ->andWhere($doctrineQb->expr()->neq($this->alias . '.id', ':irhpPermitRange'))
                ->setParameter('irhpPermitRange', $irhpPermitRange);
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Returns all non-reserved, non-replacement ranges in the specified stock
     *
     * @param int $stockId
     *
     * @return array
     */
    public function getByStockId($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ipr')
            ->from(Entity::class, 'ipr')
            ->where('ipr.ssReserve = false')
            ->andWhere('ipr.lostReplacement = false')
            ->andWhere('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds a Replacement Range by permit number and stock ID
     *
     * @param int $permitNumber
     * @param int $permitStock
     * @return array
     */
    public function fetchByPermitNumberAndStock($permitNumber, $permitStock)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias.'.irhpPermitStock', ':permitStock'))
           ->andWhere($qb->expr()->gte(':permitNumber', $this->alias.'.fromNo'))
           ->andWhere($qb->expr()->lte(':permitNumber', $this->alias.'.toNo'))
           ->andWhere($qb->expr()->eq($this->alias.'.lostReplacement', 1))
           ->setParameter('permitNumber', $permitNumber)
           ->setParameter('permitStock', $permitStock);

        return $qb->getQuery()->execute();
    }

    /**
     * Fetch a flat list of range to country associations within the specified stock
     *
     * @param int $stockId
     *
     * @return array
     */
    public function fetchRangeIdToCountryIdAssociations($stockId)
    {
        $statement = $this->getEntityManager()->getConnection()->executeQuery(
            'select iprc.irhp_permit_stock_range_id as rangeId, iprc.country_id as countryId ' .
            'from irhp_permit_range_country iprc ' .
            'inner join irhp_permit_range as r on r.id = iprc.irhp_permit_stock_range_id ' .
            'where r.irhp_permit_stock_id = :stockId',
            ['stockId' => $stockId]
        );

        return $statement->fetchAll();
    }
}
