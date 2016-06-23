<?php

namespace Dvsa\OlcsTest\Cli\Domain\QueryHandler\CommunityLic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Cli\Domain\QueryHandler\CommunityLic\CommunityLicencesForSuspensionList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Cli\Domain\Query\CommunityLic\CommunityLicencesForSuspensionList as Qry;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Mockery as m;

/**
 * Community licences for suspension list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicencesForSuspensionListTest extends QueryHandlerTestCase
{
    public function setUp()
    {
        $this->sut = new CommunityLicencesForSuspensionList();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $now = (new \DateTime())->setTime(0, 0, 0);
        $query = Qry::create(['date' => $now]);

        $mockComLic = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('result')
            ->getMock();
        $comLics = new ArrayCollection();
        $comLics->add($mockComLic);

        $this->repoMap['CommunityLic']->shouldReceive('fetchForSuspension')
            ->with($now)
            ->andReturn($comLics)
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => ['result'],
            'count' =>  1
        ];

        $this->assertEquals($result, $expected);
    }
}
