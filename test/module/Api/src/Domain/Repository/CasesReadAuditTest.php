<?php

/**
 * Cases Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\CasesReadAudit;
use Dvsa\Olcs\Transfer\Query\Audit\ReadCase;
use Mockery as m;

/**
 * Cases Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CasesReadAuditTest extends RepositoryTestCase
{
    public function setUp()
    {
        $this->setUpSut(CasesReadAudit::class, true);
    }

    public function testFetchOne()
    {
        $userId = 111;
        $entityId = 222;
        $date = '2015-01-05';

        $qb = $this->createMockQb('{{QUERY}}');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getOneOrNullResult')->andReturn(['foo']);

        $this->assertEquals(['foo'], $this->sut->fetchOne($userId, $entityId, $date));

        $expected = '{{QUERY}} AND m.user = [[111]] AND m.case = [[222]] AND m.createdOn = [[2015-01-05]]';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchList()
    {
        $queryDto = ReadCase::create(['id' => 111]);

        $qb = $this->createMockQb('{{QUERY}}');
        $this->mockCreateQueryBuilder($qb);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['result']);

        $qbh = m::mock();
        $qbh->shouldReceive('withRefdata')->once();
        $qbh->shouldReceive('paginate')->once();

        $this->queryBuilder->shouldReceive('modifyQuery')->andReturn($qbh);

        $this->assertEquals(['result'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expected = '{{QUERY}} INNER JOIN m.user u INNER JOIN u.contactDetails cd '
            . 'INNER JOIN cd.person p AND m.cases = [[111]] ORDER BY m.createdOn DESC';

        $this->assertEquals($expected, $this->query);
    }
}
