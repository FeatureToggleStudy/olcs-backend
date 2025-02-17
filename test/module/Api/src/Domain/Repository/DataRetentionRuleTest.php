<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleAdmin;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList;
use Mockery as m;

/**
 * Class DataRetentionRuleTest
 */
class DataRetentionRuleTest extends RepositoryTestCase
{
    /** @var DataRetentionRule */
    protected $sut;

    public function setUp()
    {
        $this->setUpSut(DataRetentionRule::class, true);
    }

    public function testFetchEnabledRules()
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->isNull')->with('m.deletedDate')->once()->andReturn('expr1');
        $qb->shouldReceive('andWhere')->with('expr1')->twice()->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['RESULT']);

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->andReturnSelf();

        $paginator = m::mock();
        $paginator->shouldReceive('count')->withNoArgs()->andReturn(1);
        $paginator->shouldReceive('getIterator')->andReturn('result');

        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);

        $result = $this->sut->fetchEnabledRules();

        $this->assertSame(
            [
                'results' => ['RESULT'],
                'count' => 1
            ],
            $result
        );
    }

    public function testFetchEnabledRulesWithQueryBuilderAndIsReview()
    {
        $query = RuleList::create(
            ['sort' => 'id', 'order' => 'DESC']
        );

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.actionType', ':actionType')->once()->andReturn('expr1');
        $qb->shouldReceive('expr->isNull')->with('m.deletedDate')->once()->andReturn('expr1');
        $qb->shouldReceive('andWhere')->with('expr1')->times(3)->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('actionType', 'Review')->once()->andReturn('expr1');
        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['RESULT']);

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->andReturnSelf();

        $paginator = m::mock();
        $paginator->shouldReceive('count')->withNoArgs()->andReturn(1);
        $paginator->shouldReceive('getIterator')->andReturn('result');

        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);

        $result = $this->sut->fetchEnabledRules($query, true);

        $this->assertSame(
            [
                'results' => ['RESULT'],
                'count' => 1
            ],
            $result
        );
    }

    public function testFetchAllRules()
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['RESULT']);

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->andReturnSelf();

        $paginator = m::mock();
        $paginator->shouldReceive('count')->withNoArgs()->andReturn(1);
        $paginator->shouldReceive('getIterator')->andReturn('result');

        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);

        $result = $this->sut->fetchAllRules();

        $this->assertSame(
            [
                'results' => ['RESULT'],
                'count' => 1
            ],
            $result
        );
    }

    public function testFetchAllNotDeletedRulesWithQuery()
    {
        $query = RuleAdmin::create(
            ['sort' => 'id', 'order' => 'DESC']
        );
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['RESULT']);

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->andReturnSelf();

        $paginator = m::mock();
        $paginator->shouldReceive('count')->withNoArgs()->andReturn(1);
        $paginator->shouldReceive('getIterator')->andReturn('result');

        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);

        $result = $this->sut->fetchAllRules($query);

        $this->assertSame(
            [
                'results' => ['RESULT'],
                'count' => 1
            ],
            $result
        );
    }

    public function testRunProc()
    {
        $mockedStatement = m::mock(\Doctrine\DBAL\Driver\Statement::class);
        $mockedStatement
            ->shouldReceive('rowCount')
            ->andReturn(12)
            ->shouldReceive('nextRowset')
            ->shouldReceive('execute')
            ->andReturn(true)
            ->shouldReceive('closeCursor')
            ->andReturn(true);

        $this->em
            ->shouldReceive('getConnection->getWrappedConnection->prepare')
            ->with('CALL proc(99)')
            ->once()
            ->andReturn($mockedStatement);

        $result = $this->sut->runProc('proc', 99);

        $this->assertSame(true, $result);
    }
}
