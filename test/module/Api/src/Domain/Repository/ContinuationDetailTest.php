<?php

/**
 * ContinuationDetailTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Doctrine\ORM\QueryBuilder;

/**
 * ContinuationDetailTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailTest extends RepositoryTestCase
{
    public function setUp()
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchForLicence()
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchForLicence(95));

        $dateTime = new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime();
        $year = $dateTime->format('Y');
        $futureYear = $year + 4;
        $month = $dateTime->format('n');

        $expectedQuery = <<<EOT
BLAH AND m.licence = [[95]]
    AND l.status IN [[["lsts_valid","lsts_curtailed","lsts_suspended"]]]
    AND (c.month >= [[$month]] AND c.year = [[$year]])
        OR (c.year > [[$year]] AND c.year < [[$futureYear]])
        OR (c.month <= [[$month]] AND c.year = [[$futureYear]])
    AND m.status IN ([[["con_det_sts_printed","con_det_sts_acceptable","con_det_sts_unacceptable"]]])
        OR (m.status = 'con_det_sts_complete' AND m.received = 'N')
EOT;
        // Expected query has be formatted to make it readable, need to make it non formatted for assertion
        // remove new lines
        $expectedQuery = str_replace("\n", ' ', $expectedQuery);
        // remove indentation
        $expectedQuery = str_replace("  ", '', $expectedQuery);

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchOngoingForLicence()
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getSingleResult')
                ->andReturn('RESULT')
                ->getMock()
        );
        $this->assertEquals('RESULT', $this->sut->fetchOngoingForLicence(95));

        $expectedQuery = 'BLAH AND m.licence = [[95]] AND m.status = [[con_det_sts_acceptable]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchChecklistReminders()
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->twice()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.status', 'ls')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.licenceType', 'lt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.goodsOrPsv', 'lgp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.organisation', 'lo')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.fees', 'lf')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lf.feeType', 'lfft')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('l.status', ':licenceStatuses')->once()->andReturn('conditionLic');
        $mockQb->shouldReceive('andWhere')->with('conditionLic')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with(
                'licenceStatuses',
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED
                ]
            )
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.received', 0)->once()->andReturn('conditionReceived');
        $mockQb->shouldReceive('andWhere')->with('conditionReceived')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')
            ->with('lfft.feeType', ':feeType')
            ->once()
            ->andReturn('conditionType');
        $mockQb->shouldReceive('expr->in')
            ->with('lf.feeStatus', ':feeStatus')
            ->once()
            ->andReturn('conditionStatus');
        $mockQb->shouldReceive('expr->andX')
            ->with('conditionType', 'conditionStatus')
            ->once()
            ->andReturn('conditionAndX');
        $mockQb->shouldReceive('expr->not')
            ->with('conditionAndX')
            ->once()
            ->andReturn('conditionNot');
        $mockQb->shouldReceive('expr->isNull')
            ->with('lf.id')
            ->once()->
            andReturn('conditionIsNull');
        $mockQb->shouldReceive('expr->orX')
            ->with('conditionNot', 'conditionIsNull')
            ->once()
            ->andReturn('conditionOrX');
        $mockQb->shouldReceive('andWhere')->with('conditionOrX')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('feeType', FeeTypeEntity::FEE_TYPE_CONT)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('feeStatus', [FeeEntity::STATUS_OUTSTANDING, FeeEntity::STATUS_WAIVE_RECOMMENDED])
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('filterByIds')->with([1])->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')
            ->with('c.month', ':month')
            ->once()
            ->andReturn('conditionMonth');
        $mockQb->shouldReceive('andWhere')->with('conditionMonth')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('month', 1)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')
            ->with('c.year', ':year')
            ->once()
            ->andReturn('conditionYear');
        $mockQb->shouldReceive('andWhere')->with('conditionYear')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('year', 2016)
            ->once()
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('getQuery->getResult')
            ->with(\Doctrine\ORM\Query::HYDRATE_ARRAY)
            ->once()
            ->andReturn(['result']);

        $this->sut->fetchChecklistReminders(1, 2016, [1]);
    }
}
