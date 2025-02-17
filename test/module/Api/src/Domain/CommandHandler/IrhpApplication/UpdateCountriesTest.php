<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use DateTime;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\CommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdateCountries;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries as UpdateCountriesCmd;
use Mockery as m;

class UpdateCountriesTest extends CommandHandlerTestCase
{
    public function setUp()
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);
        $this->sut = m::mock(UpdateCountries::class)->makePartial()->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    /**
     * @expectedException \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     * @expectedExceptionMessage IRHP application cannot be updated.
     */
    public function testHandleCommandWhenCannotUpdate()
    {
        $id = 1;
        $countries = ['DE', 'FR', 'NL'];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canUpdateCountries')
            ->withNoArgs()
            ->once()
            ->andReturn(false)
            ->shouldReceive('getIrhpPermitApplications')
            ->never();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpApplication)
            ->shouldReceive('saveOnFlush')
            ->never()
            ->shouldReceive('flushAll')
            ->never();

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByCountry')
            ->never();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->never()
            ->shouldReceive('deleteOnFlush')
            ->never();

        $command = UpdateCountriesCmd::create(
            [
                'id' => $id,
                'countries' => $countries,
            ]
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithoutExistingData()
    {
        $id = 1;
        $countries = ['DE', 'FR', 'NL'];

        $window1 = m::mock(IrhpPermitWindow::class);
        $window1->shouldReceive('getId')
            ->andReturn(101);

        $window2 = m::mock(IrhpPermitWindow::class);
        $window2->shouldReceive('getId')
            ->andReturn(102);

        $irhpPermitApp1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApp2 = m::mock(IrhpPermitApplication::class);

        $existingIrhpPermitApplications = [];
        $openWindows = [$window1, $window2];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canUpdateCountries')
            ->withNoArgs()
            ->once()
            ->andReturn(true)
            ->shouldReceive('getIrhpPermitApplications')
            ->withNoArgs()
            ->once()
            ->andReturn($existingIrhpPermitApplications)
            ->shouldReceive('resetCheckAnswersAndDeclaration')
            ->withNoArgs()
            ->once();

        $this->sut->shouldReceive('createIrhpPermitApplication')
            ->with($irhpApplication, $window1)
            ->once()
            ->andReturn($irhpPermitApp1)
            ->shouldReceive('createIrhpPermitApplication')
            ->with($irhpApplication, $window2)
            ->once()
            ->andReturn($irhpPermitApp2);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpApplication)
            ->shouldReceive('saveOnFlush')
            ->with($irhpApplication)
            ->once()
            ->shouldReceive('flushAll')
            ->withNoArgs()
            ->once();

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByCountry')
            ->with(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                $countries,
                m::type(DateTime::class)
            )
            ->once()
            ->andReturn($openWindows);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApp1)
            ->once()
            ->shouldReceive('saveOnFlush')
            ->with($irhpPermitApp2)
            ->once()
            ->shouldReceive('deleteOnFlush')
            ->never();

        $command = UpdateCountriesCmd::create(
            [
                'id' => $id,
                'countries' => $countries,
            ]
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($id, $result->getId('irhpApplication'));
        $this->assertEquals(
            [
                'Countries updated for IRHP application'
            ],
            $result->getMessages()
        );
    }

    public function testHandleCommandWithExistingData()
    {
        $id = 1;
        $countries = ['DE', 'FR', 'NL'];

        $window1 = m::mock(IrhpPermitWindow::class);
        $window1->shouldReceive('getId')
            ->andReturn(101);

        $window2 = m::mock(IrhpPermitWindow::class);
        $window2->shouldReceive('getId')
            ->andReturn(102);

        $window3 = m::mock(IrhpPermitWindow::class);
        $window3->shouldReceive('getId')
            ->andReturn(103);

        $irhpPermitApp1 = m::mock(IrhpPermitApplication::class);

        $irhpPermitApp2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApp2->shouldReceive('getId')
            ->andReturn(202)
            ->shouldReceive('getIrhpPermitWindow')
            ->andReturn($window2);

        $irhpPermitApp3 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApp3->shouldReceive('getId')
            ->andReturn(203)
            ->shouldReceive('getIrhpPermitWindow')
            ->andReturn($window3);

        $existingIrhpPermitApplications = [$irhpPermitApp2, $irhpPermitApp3];
        $openWindows = [$window1, $window2];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canUpdateCountries')
            ->withNoArgs()
            ->once()
            ->andReturn(true)
            ->shouldReceive('getIrhpPermitApplications')
            ->withNoArgs()
            ->once()
            ->andReturn($existingIrhpPermitApplications)
            ->shouldReceive('resetCheckAnswersAndDeclaration')
            ->withNoArgs()
            ->once();

        $this->sut->shouldReceive('createIrhpPermitApplication')
            ->with($irhpApplication, $window1)
            ->once()
            ->andReturn($irhpPermitApp1)
            ->shouldReceive('createIrhpPermitApplication')
            ->with($irhpApplication, $window2)
            ->never()
            ->shouldReceive('createIrhpPermitApplication')
            ->with($irhpApplication, $window3)
            ->never();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpApplication)
            ->shouldReceive('saveOnFlush')
            ->with($irhpApplication)
            ->once()
            ->shouldReceive('flushAll')
            ->withNoArgs()
            ->once();

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByCountry')
            ->with(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                $countries,
                m::type(DateTime::class)
            )
            ->once()
            ->andReturn($openWindows);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApp1)
            ->once()
            ->shouldReceive('saveOnFlush')
            ->with($irhpPermitApp2)
            ->never()
            ->shouldReceive('saveOnFlush')
            ->with($irhpPermitApp3)
            ->never()
            ->shouldReceive('deleteOnFlush')
            ->with($irhpPermitApp1)
            ->never()
            ->shouldReceive('deleteOnFlush')
            ->with($irhpPermitApp2)
            ->never()
            ->shouldReceive('fetchById')
            ->with(203)
            ->once()
            ->andReturn($irhpPermitApp3)
            ->shouldReceive('deleteOnFlush')
            ->with($irhpPermitApp3)
            ->once();

        $command = UpdateCountriesCmd::create(
            [
                'id' => $id,
                'countries' => $countries,
            ]
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($id, $result->getId('irhpApplication'));
        $this->assertEquals(
            [
                'Countries updated for IRHP application'
            ],
            $result->getMessages()
        );
    }
}
