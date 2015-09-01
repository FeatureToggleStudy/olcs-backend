<?php

/**
 * TmQualificationList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\QueryHandler\TmQualification\TmQualificationsList as QueryHandler;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Query\TmQualification\TmQualificationsList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;

/**
 * TmQualificationList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualificationListTest extends QueryHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TmQualification', TmQualificationRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('TransportManager', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Query::create(['transportManager' => 1]);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')
            ->with(1)->andReturn(['tm']);

        $this->repoMap['TmQualification']
            ->shouldReceive('fetchList')
            ->with($query)
            ->once()
            ->andReturn(['foo'])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->once()
            ->andReturn(1)
            ->getMock();

        $this->repoMap['Document']
            ->shouldReceive('fetchListForTm')
            ->with(1)
            ->andReturn(['doc'])
            ->once()
            ->getMock();

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
                'documents' => ['doc'],
                'transportManager' => [
                    'tm'
                ]
            ],
            $this->sut->handleQuery($query)
        );
    }
}
