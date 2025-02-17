<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrCancelled;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRegistered;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\GrantBusReg;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as CloseTasksCmd;
use Dvsa\Olcs\Transfer\Command\Bus\GrantBusReg as BusGrantBusRegCmd;
use Dvsa\Olcs\Transfer\Command\Bus\PrintLetter as BusPrintLetterCmd;
use Dvsa\Olcs\Transfer\Command\Publication\Bus as PublicationBusCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\CommandHandler\Bus\GrantBusReg
 */
class GrantBusRegTest extends CommandHandlerTestCase
{
    const BUS_REG_ID = 9999;

    /** @var  GrantBusReg */
    protected $sut;
    /** @var  m\MockInterface | BusRegEntity */
    private $mockBusReg;

    public function setUp()
    {
        $this->sut = new GrantBusReg();
        $this->mockRepo('Bus', Repository\Bus::class);

        $this->mockBusReg = m::mock(BusRegEntity::class)->makePartial();

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            BusRegEntity::STATUS_REGISTERED,
            BusRegEntity::STATUS_CANCELLED,
            'brvr_route',
        ];

        parent::initReferences();
    }

    public function testHandleCommandThrowsIncorrectStatusException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $id = 99;

        $command = BusGrantBusRegCmd::create(
            [
                'id' => $id,
                'variationReasons' => ['brvr_route'],
            ]
        );

        $this->mockBusReg->shouldReceive('getStatusForGrant')->andReturn(null);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($this->mockBusReg);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandThrowsMissingVariationReasonsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $id = 99;

        $command = BusGrantBusRegCmd::create(
            [
                'id' => $id,
            ]
        );

        $status = new RefDataEntity();
        $status->setId(BusRegEntity::STATUS_VAR);

        $this->mockBusReg
            ->shouldReceive('getStatusForGrant')->once()->andReturn(BusRegEntity::STATUS_REGISTERED)
            ->shouldReceive('getStatus')->once()->andReturn($status);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($this->mockBusReg);

        $this->sut->handleCommand($command);
    }

    /**
     * @dataProvider handleCommandProvider
     */
    public function testHandleCommand($oldStatus)
    {
        $command = BusGrantBusRegCmd::create(
            [
                'id' => self::BUS_REG_ID,
                'variationReasons' => ['brvr_route'],
            ]
        );

        $taskIds = [1, 2];

        $status = new RefDataEntity();
        $status->setId($oldStatus);

        $this->mockBusReg
            ->setId(self::BUS_REG_ID)
            ->setStatus($status)
            ->shouldReceive('canMakeDecision')->once()->andReturn(true)
            ->shouldReceive('isGrantable')->once()->andReturn(true)
            ->shouldReceive('getEbsrSubmissions')->andReturn(new ArrayCollection())
            ->shouldReceive('getLocalAuthoritys')->andReturn([1, 2, 3])
            ->shouldReceive('getOpenTaskIds')->andReturn($taskIds);

        $this->repoMap['Bus']
            ->shouldReceive('fetchUsingId')->with($command, Query::HYDRATE_OBJECT)->andReturn($this->mockBusReg)
            ->shouldReceive('save')->with(m::type(BusRegEntity::class))->once();

        $this->expectedSideEffect(
            PublicationBusCmd::class,
            ['id' => self::BUS_REG_ID],
            new Result()
        );

        $this->expectedSideEffect(
            BusPrintLetterCmd::class,
            [
                'id' => self::BUS_REG_ID,
                'printCopiesCount' => 5,
                'isEnforcePrint' => 'Y',
            ],
            new Result()
        );

        $this->expectedSideEffect(CloseTasksCmd::class, ['ids' => $taskIds], new Result());

        $actual = $this->sut->handleCommand($command);

        static::assertEquals(
            [
                'id' => [
                    'bus' => self::BUS_REG_ID,
                ],
                'messages' => [
                    'Bus Reg granted successfully',
                ],
            ],
            $actual->toArray()
        );
        $this->assertInstanceOf(Result::class, $actual);
    }

    /**
     * data provider for testHandleCommand
     *
     * @return array
     */
    public function handleCommandProvider()
    {
        return [
            [BusRegEntity::STATUS_VAR],
            [BusRegEntity::STATUS_CANCEL],
        ];
    }

    /**
     * @dataProvider handleCommandEbsrProvider
     */
    public function testHandleCommandEbsrRecord($oldStatus, $emailSideEffectClass)
    {
        $ebsrId = 55;

        $command = BusGrantBusRegCmd::create(
            [
                'id' => self::BUS_REG_ID,
                'variationReasons' => ['brvr_route'],
            ]
        );

        $status = new RefDataEntity();
        $status->setId($oldStatus);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('getId')->andReturn($ebsrId);
        $ebsrSubmissions = new ArrayCollection([$ebsrSubmission]);

        /** @var BusRegEntity $busReg */
        $this->mockBusReg
            ->setId(self::BUS_REG_ID)
            ->setStatus($status)
            ->shouldReceive('canMakeDecision')->once()->andReturn(true)
            ->shouldReceive('isGrantable')->once()->andReturn(true)
            ->shouldReceive('getEbsrSubmissions')->andReturn($ebsrSubmissions)
            ->shouldReceive('isFromEbsr')->andReturn(true)
            ->shouldReceive('getOpenTaskIds')->andReturn([]);

        $this->repoMap['Bus']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($this->mockBusReg)
            ->shouldReceive('save')->with(m::type(BusRegEntity::class))->once();

        $this->expectedSideEffect(
            PublicationBusCmd::class,
            ['id' => self::BUS_REG_ID],
            new Result()
        );

        $this->expectedSideEffect(BusPrintLetterCmd::class, ['id' => self::BUS_REG_ID], (new Result()));

        $this->expectedEmailQueueSideEffect(
            $emailSideEffectClass,
            ['id' => $ebsrId],
            $ebsrId,
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * Data provider for testHandleCommandEbsrRecord
     *
     * @return array
     */
    public function handleCommandEbsrProvider()
    {
        return [
            [BusRegEntity::STATUS_VAR, SendEbsrRegistered::class],
            [BusRegEntity::STATUS_CANCEL, SendEbsrCancelled::class],
        ];
    }
}
