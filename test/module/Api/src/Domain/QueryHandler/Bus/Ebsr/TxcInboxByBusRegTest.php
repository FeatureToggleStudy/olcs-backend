<?php

/**
 * TxcInboxByBusRegTest
 */
namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr\TxcInboxByBusReg;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox as TxcInboxEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Licence as LicenceEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as TxcInboxRepo;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxByBusReg as Qry;
use Mockery as m;
use ZfcRbac\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * TxcInboxByBusRegTest
 */
class TxcInboxByBusRegTest extends QueryHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new TxcInboxByBusReg();
        $this->mockRepo('TxcInbox', TxcInboxRepo::class);

        $this->mockedSmServices = [
            'ZfcRbac\Service\AuthorizationService' => m::mock('ZfcRbac\Service\AuthorizationService')
        ];

        parent::setUp();
    }

    private function getCurrentUser($localAuthorityId = null)
    {
        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class);
        $mockUser->shouldReceive('getUser')
            ->andReturnSelf();

        $localAuthority = new \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority();
        $localAuthority->setId($localAuthorityId);

        $mockUser->shouldReceive('getLocalAuthority')
            ->andReturn($localAuthority);

        return $mockUser;
    }

    public function testHandleQuery()
    {
        $query = Qry::create(
            [
                'busReg' => 2
            ]
        );

        $this->mockedSmServices['ZfcRbac\Service\AuthorizationService']
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($this->getCurrentUser(4));

        $mockLicence = m::mock();
        $mockLicence->shouldReceive('determineNpNumber')->andReturn('333');

        $mockResult = new TxcInboxEntity();
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->shouldReceive('isLatestVariation')->andReturn(false);
        $busReg->shouldReceive('isScottishRules')->andReturn(false);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getLatestBusVariation');
        $licence->shouldReceive('determineNpNumber')
            ->once()
            ->andReturn('4321');

        $busReg->setLicence($licence);

        $mockResult->setBusReg($busReg);

        $this->repoMap['TxcInbox']->shouldReceive('fetchListForLocalAuthorityByBusReg')
            ->with($query->getBusReg(), 4)
            ->andReturn([0 => $mockResult]);

        $this->sut->handleQuery($query);
    }

    /**
     * @expectedException \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     */
    public function testHandleQueryNotFoundException()
    {
        $query = Qry::create(
            [
                'busReg' => 2
            ]
        );

        $this->mockedSmServices['ZfcRbac\Service\AuthorizationService']
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($this->getCurrentUser(4));

        $this->repoMap['TxcInbox']->shouldReceive('fetchListForLocalAuthorityByBusReg')
            ->with($query->getBusReg(), 4)
            ->andReturnNull();

        $this->sut->handleQuery($query);
    }
}
