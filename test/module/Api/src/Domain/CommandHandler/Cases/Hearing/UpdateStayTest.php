<?php

/**
 * Update Stay Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing\UpdateStay;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\UpdateStay as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Stay as StayEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as AppealEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Update Stay Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateStayTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new UpdateStay();
        $this->mockRepo('Stay', StayEntity::class);
        $this->mockRepo('Cases', CasesEntity::class);
        $this->mockRepo('Appeal', AppealEntity::class);

        $this->refData = [
            'stay_t_ut',
        ];

        parent::setUp();
    }

    public function testHandleCommand()
    {
        $mockAppeal = m::mock(AppealEntity::class);
        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockStay = m::mock(StayEntity::class)->makePartial();
        $mockCase->shouldReceive('getAppeals')
            ->andReturn(new ArrayCollection([$mockAppeal]));

        $mockCase->shouldReceive('getStays')
            ->andReturn(new ArrayCollection([$mockStay]));

        $this->references = [
            CasesEntity::class => [
                24 => $mockCase
            ]
        ];

        parent::initReferences();

        $command = Cmd::create(
            [
                "case" => 24,
                "stayType" => "stay_t_ut",
                "requestDate" => "2015-01-05",
                "decisionDate" => "2015-01-09",
                "outcome" => "stay_s_granted",
                "notes" => "booo",
                "isWithdrawn" => "Y",
                "withdrawnDate" => "2015-05-05"
            ]
        );

        /** @var StayEntity $appeal */
        $stay = m::mock(StayEntity::class)->makePartial();
        $stay->setId($command->getId());

        $this->repoMap['Stay']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($stay)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(StayEntity::class))
            ->andReturnUsing(
                function (StayEntity $stay) use (&$s) {
                    $s = $stay;
                    $stay->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf('Dvsa\Olcs\Api\Domain\Command\Result', $result);
        $this->assertObjectHasAttribute('ids', $result);
        $this->assertObjectHasAttribute('messages', $result);
        $this->assertContains('Stay updated', $result->getMessages());
    }
}
