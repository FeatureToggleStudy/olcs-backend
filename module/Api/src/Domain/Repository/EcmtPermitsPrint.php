<?php

/**
 * ECMT Permits
 *
 * @author Kollol Shamsuddin <kol.shamsuddin@capgemini.com>
 */
namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\EcmtPermits as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class EcmtPermitsPrint extends AbstractRepository
{

    protected $entity = Entity::class;
    protected $countries = ["","Austria","Switzerland","Croatia","Bulgaria","Albania","Netherlands"];

    /**
     * Applies filters
     *
     * @param QueryBuilder   $qb    doctrine query builder
     * @param QueryInterface $query query being run
     *
     * @return array
     */
    public function fetchData($query)
    {

        $hydrateMode = Query::HYDRATE_OBJECT;

        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->order($this->alias .'.'. $query->getSort(),$query->getOrder())
            ->paginate($query->getPage(), $query->getLimit());

        $qb->andWhere($qb->expr()->eq($this->alias . '.sectorId', ':bySector'))->setParameter('bySector', $query->getSectorId());

        $results = $this->fetchPaginatedObj($qb, $hydrateMode);

        $data = [];
        foreach ($results as $row)
        {
            $r = $row->getEcmtPermitsApplication()->getLicence()->getLicNo();
            $rr = $row->getEcmtPermitsApplication()->getLicence()->getOrganisation()->getName();
            $row->setEcmtPermitsApplication($r);
            $row->setStartDate($rr);

            $country = explode(",",$row->getEcmtCountriesIds());

            if(is_array($country) && strlen($country[0]) < 3){
                $items = [];
                foreach($country as $num)
                {
                    $items[] = $this->countries[$num];
                }
                $row->setEcmtCountriesIds(implode(", ",$items));
            }
            elseif (!is_array($country) && strlen($country[0]) < 3)
            {
                $row->setEcmtCountriesIds($this->countries[$row->getEcmtCountriesIds()]);
            }
            $data[] = $row;
        }

        return [
            'result' => new \ArrayIterator($data),
            'count' => $this->fetchPaginatedCount($qb)
        ];

    }

    /**
     * Abstracted paginator logic so it can be re-used with alternative queries
     *
     * @param QueryBuilder $qb          Doctrine query builder
     * @param int          $hydrateMode Hydrate mode
     *
     * @return object
     */
    public function fetchPaginatedObj(QueryBuilder $qb, $hydrateMode = Query::HYDRATE_ARRAY)
    {
        $query = $qb->getQuery();
        $query->setHydrationMode($hydrateMode);

        if ($this->query instanceof PagedQueryInterface) {
            $paginator = $this->getPaginator($query);

            return $paginator->getIterator($hydrateMode);
        }

        return $query->getResult($hydrateMode);
    }
}
