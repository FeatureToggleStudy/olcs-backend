<?php

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Cli\Domain\Command\Bus\Expire as ExpireBusCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Bus\Expire as ExpireHandler;
use Mockery as m;

/**
 * Bus reg expiry command handler test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ExpireTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new ExpireHandler();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    /**
     * tests handleCommand
     */
    public function testHandleCommand()
    {
        $this->repoMap['Bus']->shouldReceive('expireRegistrations')->withNoArgs();
        $result = $this->sut->handleCommand(ExpireBusCmd::create([]));

        $messages = [0 => 'registrations have been expired'];
        $this->assertEquals($messages, $result->getMessages());
    }
}
