<?php

/**
 * Update MyAccount Internal Test
 */
namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\MyAccount;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\MyAccount\UpdateMyAccountInternal as Sut;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccount as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;

/**
 * Update MyAccount Internal Test
 */
class UpdateMyAccountInternalTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new Sut();

        parent::setUp();
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'team' => 1,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                ],
            ],
        ];

        $command = Cmd::create($data);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\MyAccount\UpdateMyAccount::class,
            $data,
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
