<?php

/**
 * DeletePublicationLinkTest.php
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\DeletePublicationLink;
use Dvsa\Olcs\Transfer\Command\Publication\DeletePublicationLink as Cmd;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;

/**
 * Class DeletePublicationLinkTest
 */
class DeletePublicationLinkTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new DeletePublicationLink();
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);

        parent::setUp();
    }

    /**
     * tests deleting a publication link record
     */
    public function testHandleCommand()
    {
        $id = 34;
        $data = ['id' => $id];
        $command = Cmd::create($data);

        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('isNew')->once()->andReturn(true);

        $publicationLink = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink->setPublication($publication);
        $publicationLink->setId($id);
        $publicationLink->shouldReceive('getPoliceDatas->clear')->once()->andReturnSelf();

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($publicationLink);

        $this->repoMap['PublicationLink']
            ->shouldReceive('delete')
            ->with($publicationLink)
            ->once()
            ->andReturnSelf();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'PublicationLink' => $id
            ],
            'messages' => [
                'Publication entry deleted successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @expectedException \Dvsa\Olcs\Api\Domain\Exception\ForbiddenException
     */
    public function testHandleCommandThrowsException()
    {
        $command = Cmd::create(['id' => 34]);

        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('isNew')->once()->andReturn(false);

        $publicationLink = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink->setPublication($publication);

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($publicationLink);

        $this->sut->handleCommand($command);
    }
}
