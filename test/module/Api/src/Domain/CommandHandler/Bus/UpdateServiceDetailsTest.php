<?php

/**
 * Update Service Details Test
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\UpdateServiceDetails;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\BusNoticePeriod as BusNoticePeriodRepo;
use Dvsa\Olcs\Api\Domain\Repository\BusRegOtherService as BusRegOtherServiceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\CreateBusFee as CmdCreateBusFee;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService as BusRegOtherServiceEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusServiceType as BusServiceTypeEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;

/**
 * Update Service DetailsTest
 */
class UpdateServiceDetailsTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new UpdateServiceDetails();
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('BusNoticePeriod', BusNoticePeriodRepo::class);
        $this->mockRepo('BusRegOtherService', BusRegOtherServiceRepo::class);
        $this->mockRepo('Fee', FeeRepo::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->references = [
            BusNoticePeriodEntity::class => [
                2 => m::mock(BusNoticePeriodEntity::class)
            ],
            BusServiceTypeEntity::class => [
                5 => m::mock(BusServiceTypeEntity::class)
            ]
        ];

        parent::initReferences();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand()
    {
        $busRegId = 99;
        $serviceNumber = 12345;
        $startPoint = 'start point';
        $finishPoint = 'finish point';
        $via = 'via';
        $otherDetails = 'other details';
        $receivedDate = '';
        $effectiveDate = '';
        $endDate = '';
        $busNoticePeriod = 2;
        $otherServices = [
            0 => [
                'id' => 1,
                'version' => 1,
                'serviceNo' => 99999
            ],
            1 => [
                'id' => null,
                'version' => 1,
                'serviceNo' => 88888
            ],
            2 => [
                'id' => 2,
                'version' => 1,
                'serviceNo' => null
            ]
        ];
        $busServiceTypes = [
            0 => 5
        ];

        $command = Cmd::Create(
            [
                'id' => $busRegId,
                'serviceNumber' => $serviceNumber,
                'startPoint' => $startPoint,
                'finishPoint' => $finishPoint,
                'via' => $via,
                'otherDetails' => $otherDetails,
                'receivedDate' => $receivedDate,
                'effectiveDate' => $effectiveDate,
                'endDate' => $endDate,
                'busNoticePeriod' => $busNoticePeriod,
                'otherServices' => $otherServices,
                'busServiceTypes' => $busServiceTypes
            ]
        );

        /** @var BusRegOtherServiceEntity $busReg */
        $mockBusRegOtherServiceEntity = m::mock(BusRegOtherServiceEntity::class);
        $mockBusRegOtherServiceEntity->shouldReceive('setServiceNo');
        $mockBusRegOtherServiceEntity->shouldReceive('getId');

        /** @var BusRegOtherServiceEntity $busReg */
        $mockBusRegObjectOtherServiceEntity = m::mock(BusRegOtherServiceEntity::class);
        $mockBusRegObjectOtherServiceEntity->shouldReceive('getId')->andReturn(123);

        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->shouldReceive('updateServiceDetails')
            ->once()
            ->shouldReceive('getId')
            ->andReturn($busRegId)
            ->shouldReceive('setBusServiceTypes')
            ->with(m::type(ArrayCollection::class))
            ->once()
            ->shouldReceive('getOtherServices')
            ->andReturn([0 => $mockBusRegObjectOtherServiceEntity]);

        $this->repoMap['Fee']->shouldReceive('getLatestFeeForBusReg')
            ->with($busRegId)
            ->andReturn(['fee']);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusRegEntity::class))
            ->once();

        $this->repoMap['BusRegOtherService']->shouldReceive('fetchById')
            ->andReturn($mockBusRegOtherServiceEntity)
            ->shouldReceive('save')
            ->with(m::type(BusRegOtherServiceEntity::class))
            ->shouldReceive('delete')
            ->with(m::type(BusRegOtherServiceEntity::class));

        $mockBusNoticePeriodEntity = m::mock(BusNoticePeriodEntity::class);

        $this->repoMap['BusNoticePeriod']->shouldReceive('fetchById')
            ->andReturn($mockBusNoticePeriodEntity);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf('Dvsa\Olcs\Api\Domain\Command\Result', $result);
    }
}
