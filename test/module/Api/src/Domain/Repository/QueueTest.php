<?php

/**
 * Queue test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * Queue test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class QueueTest extends RepositoryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\Queue
     */
    protected $sut;

    public function setUp()
    {
        $this->setUpSut(QueueRepo::class, true);
    }

    public function testGetNextItem()
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $ref = m::mock(RefData::class)->makePartial();
        $this->sut->shouldReceive('getRefdataReference')
            ->with(QueueEntity::STATUS_PROCESSING)
            ->once()
            ->andReturn($ref);
        $this->sut
            ->shouldReceive('save')
            ->with($item)
            ->once();

        $this->assertEquals($item, $this->sut->getNextItem());

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND '.
            '(q.processAfterDate <= [['. $now->format(DateTime::W3C) .']] OR q.processAfterDate IS NULL) LIMIT 1';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testGetNextItemInclude()
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $ref = m::mock(RefData::class)->makePartial();
        $this->sut->shouldReceive('getRefdataReference')
            ->with(QueueEntity::STATUS_PROCESSING)
            ->once()
            ->andReturn($ref);
        $this->sut
            ->shouldReceive('save')
            ->with($item)
            ->once();

        $this->assertEquals($item, $this->sut->getNextItem(['foo']));

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND'.
            ' (q.processAfterDate <= [['. $now->format(DateTime::W3C) .']] OR q.processAfterDate IS NULL) LIMIT 1'.
            ' AND q.type IN [[["foo"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testGetNextItemExclude()
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $ref = m::mock(RefData::class)->makePartial();
        $this->sut->shouldReceive('getRefdataReference')
            ->with(QueueEntity::STATUS_PROCESSING)
            ->once()
            ->andReturn($ref);
        $this->sut
            ->shouldReceive('save')
            ->with($item)
            ->once();

        $this->assertEquals($item, $this->sut->getNextItem(['foo'], ['bar']));

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND'.
            ' (q.processAfterDate <= [['. $now->format(DateTime::W3C) .']] OR q.processAfterDate IS NULL) LIMIT 1'.
            ' AND q.type IN [[["foo"]]] AND q.type NOT IN [[["bar"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testEnqueueContinuationNotSought()
    {
        $options1 = '{"id":1,"version":2}';
        $options2 = '{"id":3,"version":4}';

        $query = 'INSERT INTO `queue` (`status`, `type`, `options`) VALUES '
            . '(:status1, :type1, :options1), (:status2, :type2, :options2)';

        $params = [
            'status1' => QueueEntity::STATUS_QUEUED,
            'type1' => QueueEntity::TYPE_CNS,
            'options1' => $options1,
            'status2' => QueueEntity::STATUS_QUEUED,
            'type2' => QueueEntity::TYPE_CNS,
            'options2' => $options2
        ];

        $mockStatement = m::mock()
            ->shouldReceive('execute')
            ->with($params)
            ->once()
            ->shouldReceive('rowCount')
            ->andReturn(2)
            ->once()
            ->getMock();

        $mockConnection = m::mock()
            ->shouldReceive('prepare')
            ->with($query)
            ->andReturn($mockStatement)
            ->once()
            ->getMock();

        $this->em->shouldReceive('getConnection')
            ->andReturn($mockConnection)
            ->once()
            ->getMock();

        $licences = [
            ['id' => 1, 'version' => 2],
            ['id' => 3, 'version' => 4]
        ];

        $this->assertEquals(2, $this->sut->enqueueContinuationNotSought($licences));
    }
}
