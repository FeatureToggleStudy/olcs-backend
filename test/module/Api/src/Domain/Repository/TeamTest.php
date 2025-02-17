<?php

/**
 * Team repo test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;

/**
 * Team repo test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamTest extends RepositoryTestCase
{
    public function setUp()
    {
        $this->setUpSut(TeamRepo::class);
    }

    public function testFetchByName()
    {
        $name = 'foo';

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('m.name', ':name')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('name', $name)->once();

        $mockQb->shouldReceive('getQuery->getResult')->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchByName($name));
    }

    public function testFetchWithPrinters()
    {
        $id = 1;

        $mockQb = m::mock(QueryBuilder::class);
        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->with($id)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('teamPrinters', 'tp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('tp.printer', 'tpp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('tp.user', 'pu')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('tp.subCategory', 'ps')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')->andReturn('result');

        $this->assertSame('result', $this->sut->fetchWithPrinters($id, 1));
    }
}
