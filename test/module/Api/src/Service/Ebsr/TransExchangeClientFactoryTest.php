<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClientFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Zend\Http\Client as RestClient;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class TransExchangeClientFactoryTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr
 */
class TransExchangeClientFactoryTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testCreateServiceNoConfig()
    {
        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn([]);

        $sut = new TransExchangeClientFactory();
        $sut->createService($mockSl);
    }

    public function testCreateService()
    {
        $config = [
            'transexchange_publisher' => [
                'uri' => 'http://localhost:8080/txc/',
                'options' => ['argseparator' => '~'],
                'template_file' => 'template.xml'
            ]
        ];

        $mockSpec = m::mock(SpecificationInterface::class);
        $mockFilter = m::mock(MapXmlFile::class);
        $mockFilter->shouldReceive('setMapping')->with($mockSpec);

        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn(['ebsr'=>$config]);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('TransExchangePublisherXmlMapping')->andReturn($mockSpec);
        $mockSl->shouldReceive('get')->with(MapXmlFile::class)->andReturn($mockFilter);

        $sut = new TransExchangeClientFactory();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf(TransExchangeClient::class, $service);
    }
}
