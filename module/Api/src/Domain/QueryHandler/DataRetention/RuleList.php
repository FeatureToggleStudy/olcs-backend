<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Rule List
 */
class RuleList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DataRetentionRule';

    /**
     * Return list of data retention rules that are enabled and action is 'Review'
     *
     * @param QueryInterface $query Query for data retention rule list
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var DataRetentionRule $repo */
        $repo = $this->getRepo();

        $enabledRulesInReview = $repo->fetchEnabledRules();

        return [
            'result' => $this->resultList(
                $enabledRulesInReview
            ),
            'count' => count($enabledRulesInReview)
        ];
    }
}
