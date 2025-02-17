<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\RequestMapQueue;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\RequestMap as RequestMapCmd;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Identity\IdentityInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;

/**
 * RequestMapQueueTest
 */
class RequestMapQueueTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new RequestMapQueue();
        $this->mockRepo('Bus', BusRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    /**
     * Tests EBSR map requests are queued correctly
     */
    public function testHandleCommand()
    {
        $busRegId = 123;
        $userId = 456;
        $licenceId = 789;
        $regNo = '123/4567';
        $scale = 'small';
        $fromNewEbsr = true;

        $cmd = RequestMapCmd::create(
            [
                'id' => $busRegId,
                'scale' => $scale,
                'fromNewEbsr' => $fromNewEbsr
            ]
        );

        $busRegEntity = m::mock(BusRegEntity::class);
        $busRegEntity->shouldReceive('getRegNo')->once()->andReturn($regNo);
        $busRegEntity->shouldReceive('getLicence->getId')->once()->andReturn($licenceId);
        $busRegEntity->shouldReceive('getEbsrSubmissions->isEmpty')->once()->andReturn(false);

        $user = m::mock(UserEntity::class);
        $user->shouldReceive('getId')->andReturn($userId);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser')->once()->andReturn($user);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $this->repoMap['Bus']
            ->shouldReceive('fetchUsingId')
            ->with($cmd)
            ->once()
            ->andReturn($busRegEntity);

        $optionData = [
            'scale' => $scale,
            'id' => $busRegId,
            'fromNewEbsr' => $fromNewEbsr,
            'regNo' => $regNo,
            'licence' => $licenceId,
            'user' => $userId
        ];

        $this->expectedQueueSideEffect($busRegId, Queue::TYPE_EBSR_REQUEST_MAP, $optionData);

        $result = $this->sut->handleCommand($cmd);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($result->getMessages()[0], RequestMapQueue::CONFIRM_MESSAGE);
    }

    /**
     * Tests exception thrown when no ebsr submission present
     *
     * @expectedException \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     */
    public function testHandleCommandNoSubmission()
    {
        $busRegId = 123;
        $scale = 'small';

        $cmd = RequestMapCmd::create(
            [
                'id' => $busRegId,
                'scale' => $scale
            ]
        );

        $busRegEntity = m::mock(BusRegEntity::class);
        $busRegEntity->shouldReceive('getEbsrSubmissions->isEmpty')->once()->andReturn(true);

        $this->repoMap['Bus']
            ->shouldReceive('fetchUsingId')
            ->with($cmd)
            ->once()
            ->andReturn($busRegEntity);

        $this->sut->handleCommand($cmd);
    }
}
