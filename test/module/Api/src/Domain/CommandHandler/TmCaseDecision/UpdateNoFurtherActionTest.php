<?php

/**
 * Update NoFurtherAction Test
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision\UpdateNoFurtherAction;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as TmCaseDecisionEntity;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\UpdateNoFurtherAction as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Doctrine\ORM\Query;
use Mockery as m;

/**
 * Update NoFurtherAction Test
 */
class UpdateNoFurtherActionTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new UpdateNoFurtherAction();
        $this->mockRepo('TmCaseDecision', TmCaseDecision::class);

        parent::setUp();
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'noFurtherActionReason' => 'testing',
        ];

        $command = Cmd::create($data);

        $decision = new RefData();
        $decision->setId(TmCaseDecisionEntity::DECISION_NO_FURTHER_ACTION);

        /** @var TmCaseDecisionEntity $tmCaseDecision */
        $tmCaseDecision = m::mock(TmCaseDecisionEntity::class)->makePartial();
        $tmCaseDecision->setId(111);
        $tmCaseDecision->setDecision($decision);
        $tmCaseDecision->shouldReceive('update')
            ->once()
            ->with($data)
            ->andReturnSelf();

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($tmCaseDecision);

        $this->repoMap['TmCaseDecision']->shouldReceive('save')
            ->once()
            ->with(m::type(TmCaseDecisionEntity::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'tmCaseDecision' => 111,
            ],
            'messages' => [
                'Decision updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @expectedException \Dvsa\Olcs\Api\Domain\Exception\BadRequestException
     */
    public function testHandleCommandThrowsIncorrectActionException()
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'noFurtherActionReason' => 'testing',
        ];

        $command = Cmd::create($data);

        $decision = new RefData();
        $decision->setId(TmCaseDecisionEntity::DECISION_REPUTE_NOT_LOST);

        /** @var TmCaseDecisionEntity $tmCaseDecision */
        $tmCaseDecision = m::mock(TmCaseDecisionEntity::class)->makePartial();
        $tmCaseDecision->setId(111);
        $tmCaseDecision->setDecision($decision);

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($tmCaseDecision);

        $this->sut->handleCommand($command);
    }
}
