<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Snapshot;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Surrender\SubmitForm as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\SubmitForm as sut;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\Category;

class SubmitFormTest extends CommandHandlerTestCase
{
    /** @var Sut */
    protected $sut;

    public function setUp()
    {
        $this->sut = new Sut();
        $this->refData = [];
        $this->mockRepo('Surrender', \Dvsa\Olcs\Api\Domain\Repository\Surrender::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockedSmServices = [
            \ZfcRbac\Service\AuthorizationService::class => m::mock(\ZfcRbac\Service\AuthorizationService::class)
        ];
        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            RefData::SIG_PHYSICAL_SIGNATURE,
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 65,
        ];
        $command = Cmd::create($data);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Surrender\Update::class,
            [
                'signatureType' => RefData::SIG_PHYSICAL_SIGNATURE,
                'id' => 65,
                'status' => Surrender::SURRENDER_STATUS_SIGNED,
            ],
            new Result()
        );

        $surrenderEntity = m::mock(Surrender::class);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->with($command->getId(), Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($surrenderEntity);

        $surrenderEntity->shouldReceive('getId')
            ->andReturn(5);

        $this->expectedSideEffect(
            Snapshot::class,
            [
                'id' => $command->getId()
            ],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
                'description' => 'Digital surrender',
                'isClosed' => 'N',
                'urgent' => 'N',
                'licence' => $command->getId(),
                'surrender' => 5
            ],
            new Result()
        );


        $licence = m::mock(Licence::class)
            ->shouldReceive('setStatus')
            ->once()
            ->getMock();


        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with(65)
            ->once()
            ->andReturn($licence);

        $this->repoMap['Licence']
            ->shouldReceive('save')
            ->with($licence)
            ->once();

        $this->sut->handleCommand($command);
    }
}
