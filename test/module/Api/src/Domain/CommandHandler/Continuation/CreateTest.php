<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Continuation;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Continuation\Create as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Continuation\Create as Command;
use Dvsa\Olcs\Api\Domain\Repository\Continuation as ContinuationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Entity\Licence\Continuation as ContinuationEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Mockery as m;

/**
 * Create continuations test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Continuation', ContinuationRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            ContinuationDetailEntity::STATUS_PREPARED,
        ];
        $this->references = [
            TrafficAreaEntity::class => [
                'B' => m::mock(TrafficAreaEntity::class)
            ],
            LicenceEntity::class => [
                1 => m::mock(LicenceEntity::class)->makePartial()
            ],
            ContinuationEntity::class => [
                11 => m::mock(ContinuationEntity::class)->makePartial()
            ]
        ];
        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $month = 1;
        $year = 2015;
        $trafficArea = 'B';
        $data = [
            'month' => $month,
            'year'  => $year,
            'trafficArea' => $trafficArea
        ];
        $command = Command::create($data);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchContinuation')
            ->with($month, $year, $trafficArea)
            ->andReturn([])
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchForContinuation')
            ->with($year, $month, $trafficArea)
            ->andReturn(['licence'])
            ->once()
            ->getMock();

        $cont = null;
        $this->repoMap['Continuation']->shouldReceive('save')
            ->with(m::type(ContinuationEntity::class))
            ->andReturnUsing(
                function (ContinuationEntity $continuation) use (&$cont) {
                    $cont = $continuation;
                    $continuation->setId(11);
                    $continuation->setYear(2015);
                    $continuation->setMonth(1);
                    $continuation->setTrafficArea($this->mapReference(TrafficAreaEntity::class, 'B'));
                }
            );

        $contDet = null;
        $this->repoMap['ContinuationDetail']->shouldReceive('save')
            ->with(m::type(ContinuationDetailEntity::class))
            ->andReturnUsing(
                function (ContinuationDetailEntity $continuationDetail) use (&$contDet) {
                    $contDet = $continuationDetail;
                    $continuationDetail->setId(22);
                    $continuationDetail->setLicence($this->mapReference(LicenceEntity::class, 1));
                    $continuationDetail->setReceived('N');
                    $continuationDetail->setStatus($this->mapRefData(ContinuationDetailEntity::STATUS_PREPARED));
                    $continuationDetail->setContinuation($this->mapReference(ContinuationEntity::class, 11));
                }
            );

        $expected = [
            'id' => [
                'continuation' => 11,
            ],
            'messages' => ['Continuation created']
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }

    public function testContinuationExists()
    {
        $month = 1;
        $year = 2015;
        $trafficArea = 'B';
        $data = [
            'month' => $month,
            'year'  => $year,
            'trafficArea' => $trafficArea
        ];
        $command = Command::create($data);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchContinuation')
            ->with($month, $year, $trafficArea)
            ->andReturn([m::mock()->shouldReceive('getId')->andReturn(11)->once()->getMock()])
            ->once()
            ->getMock();

        $expected = [
            'id' => [
                'continuation' => 11,
            ],
            'messages' => ['Continuation exists']
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }

    public function testNoLicencesFound()
    {
        $month = 1;
        $year = 2015;
        $trafficArea = 'B';
        $data = [
            'month' => $month,
            'year'  => $year,
            'trafficArea' => $trafficArea
        ];
        $command = Command::create($data);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchContinuation')
            ->with($month, $year, $trafficArea)
            ->andReturn([])
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchForContinuation')
            ->with($year, $month, $trafficArea)
            ->andReturn([])
            ->once()
            ->getMock();

        $expected = [
            'id' => [
                'continuation' => 0,
            ],
            'messages' => ['No licences found']
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }
}
