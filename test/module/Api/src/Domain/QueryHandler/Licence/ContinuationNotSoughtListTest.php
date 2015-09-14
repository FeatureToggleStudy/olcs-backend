<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\ContinuationNotSoughtList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Transfer\Query\Licence\ContinuationNotSoughtList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * ContinuationNotSoughtTest
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ContinuationNotSoughtListTest extends QueryHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Query::create(['QUERY']);

        $licence = m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class)
            ->shouldReceive('serialize')
            ->with(['trafficArea'])
            ->once()
            ->andReturn('SERIALIZED')
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchForContinuationNotSought')
            ->andReturn([$licence]);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertEquals('1', $result['count']);
    }
}
