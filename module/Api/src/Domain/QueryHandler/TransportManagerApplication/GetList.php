<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of Transport Manager Applications
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerApplication';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $query \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList */

        /* @var $repo \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT), ['application' => ['licence']]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
