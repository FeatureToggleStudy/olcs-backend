<?php

/**
 * Penalty Test
 */
namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Si\Applied;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Si\Applied\Penalty as PenaltyHandler;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\SiPenalty as SiPenaltyRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Si\Applied\Penalty as Qry;

/**
 * Penalty Test
 */
class PenaltyTest extends QueryHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new PenaltyHandler();
        $this->mockRepo('SiPenalty', SiPenaltyRepo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Qry::create(['id' => 111]);

        $siPenalty = m::mock(SiPenalty::class)->makePartial();
        $siPenalty->shouldReceive('serialize')
            ->andReturn(['foo']);

        $this->repoMap['SiPenalty']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($siPenalty);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['foo'], $result->serialize());
    }
}
