<?php

namespace OlcsTest\Api;

use Dvsa\Olcs\Api\Module;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use phpseclib\Crypt\Base;
use Zend\EventManager\Event;
use Zend\Mvc\Application;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\Types\EncryptedStringType;

/**
 * Tests the Api Module php
 */
class ModuleTest extends MockeryTestCase
{
    /** @var  Module */
    private $sut;

    public function setUp()
    {
        $this->sut = m::mock(Module::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testOnBootstrap()
    {
        $loginId = 123;

        $mockShm = m::mock();
        $mockShm->shouldReceive('attach')->once()
            ->with(
                'Zend\Mvc\SendResponseListener',
                SendResponseEvent::EVENT_SEND_RESPONSE,
                m::type('callable')
            );

        $mockEm = m::mock();
        $mockEm->shouldReceive('getSharedManager')->andReturn($mockShm);

        $mockPvl = m::mock();
        $mockPvl->shouldReceive('attach')->with($mockEm, 1)->once();

        $mockAuth = m::mock(AuthorizationService::class);
        $mockAuth->shouldReceive('getIdentity->getUser->getLoginId')->once()->andReturn($loginId);

        $mockLog = m::mock();
        $mockLog->shouldReceive('get')->with(\Olcs\Logging\Log\Processor\UserId::class)->once()->andReturnSelf();
        $mockLog->shouldReceive('setUserId')->with($loginId)->once();

        $mockSm = m::mock(ServiceManager::class);
        $mockSm->shouldReceive('get')->with('PayloadValidationListener')->andReturn($mockPvl);
        $mockSm->shouldReceive('get')->with(AuthorizationService::class)->andReturn($mockAuth);
        $mockSm->shouldReceive('get')->with('LogProcessorManager')->andReturn($mockLog);
        $mockSm->shouldReceive('get')->with('config')->andReturn([]);

        $mockApp = m::mock(Application::class);
        $mockApp->shouldReceive('getServiceManager')->andReturn($mockSm);
        $mockApp->shouldReceive('getEventManager')->andReturn($mockEm);

        $mockEvent = m::mock(Event::class);
        $mockEvent->shouldReceive('getApplication')->andReturn($mockApp);

        $this->sut->onBootstrap($mockEvent);
    }

    public function testLogResponseHttp()
    {
        $logWriter = $this->setupLogger();

        $mockRespone = m::mock(\Zend\Http\PhpEnvironment\Response::class);
        $mockRespone->shouldReceive('getContent')->with()->once()->andReturn('CONTENT');
        $mockRespone->shouldReceive('getStatusCode')->with()->twice()->andReturn(200);

        $this->sut->logResponse($mockRespone);

        $this->assertCount(1, $logWriter->events);
        $this->assertSame(\Zend\Log\Logger::DEBUG, $logWriter->events[0]['priority']);
        $this->assertSame('API Response Sent', $logWriter->events[0]['message']);
        $this->assertSame(['status' => 200, 'content' => 'CONTENT'], $logWriter->events[0]['extra']);
    }

    public function testLogResponseHttpEmptyOlcsDownloadHeader()
    {
        $logWriter = $this->setupLogger();

        $mockRespone = m::mock(\Zend\Http\PhpEnvironment\Response::class);
        $mockRespone->shouldReceive('getContent')->with()->once()->andReturn('');
        $mockRespone->shouldReceive('getStatusCode')->with()->andReturn(206);

        $this->sut->logResponse($mockRespone);

        $this->assertCount(1, $logWriter->events);
        $this->assertNotContains('API Response is empty', current($logWriter->events));
    }

    public function testLogResponseCli()
    {
        $logWriter = $this->setupLogger();

        $mockRespone = m::mock(\Zend\Console\Response::class);
        $mockRespone->shouldReceive('getContent')->with()->once()->andReturn('CONTENT');
        $mockRespone->shouldReceive('getErrorLevel')->with()->twice()->andReturn(0);

        $this->sut->logResponse($mockRespone);

        $this->assertCount(1, $logWriter->events);
        $this->assertSame(\Zend\Log\Logger::DEBUG, $logWriter->events[0]['priority']);
        $this->assertSame('CLI Response Sent', $logWriter->events[0]['message']);
        $this->assertSame(['errorLevel' => 0, 'content' => 'CONTENT'], $logWriter->events[0]['extra']);
    }

    public function testLogResponseCliError()
    {
        $logWriter = $this->setupLogger();

        $mockRespone = m::mock(\Zend\Console\Response::class);
        $mockRespone->shouldReceive('getContent')->with()->once()->andReturn('CONTENT');
        $mockRespone->shouldReceive('getErrorLevel')->with()->twice()->andReturn(1);

        $this->sut->logResponse($mockRespone);

        $this->assertCount(1, $logWriter->events);
        $this->assertSame(\Zend\Log\Logger::ERR, $logWriter->events[0]['priority']);
        $this->assertSame('CLI Response Sent', $logWriter->events[0]['message']);
        $this->assertSame(['errorLevel' => 1, 'content' => 'CONTENT'], $logWriter->events[0]['extra']);
    }

    public function testLogResponseContentLong()
    {
        $logWriter = $this->setupLogger();

        $content = str_repeat('X', 1010);

        $mockRespone = m::mock(\Zend\Http\PhpEnvironment\Response::class);
        $mockRespone->shouldReceive('getContent')->with()->once()->andReturn($content);
        $mockRespone->shouldReceive('getStatusCode')->with()->twice()->andReturn(200);

        $this->sut->logResponse($mockRespone);

        $this->assertCount(1, $logWriter->events);
        $this->assertSame(\Zend\Log\Logger::DEBUG, $logWriter->events[0]['priority']);
        $this->assertSame('API Response Sent', $logWriter->events[0]['message']);
        $this->assertSame(str_repeat('X', 1000) .'...', $logWriter->events[0]['extra']['content']);
    }

    public function testInitDoctrineEncrypterType()
    {
        if (!EncryptedStringType::hasType(EncryptedStringType::TYPE)) {
            EncryptedStringType::addType(EncryptedStringType::TYPE, EncryptedStringType::class);
        }
        $this->sut->initDoctrineEncrypterType(['olcs-doctrine' => ['encryption_key' => 'key']]);

        /** @var Base $ciper */
        $ciper = \Doctrine\DBAL\Types\Type::getType('encrypted_string')->getEncrypter();

        $this->assertInstanceOf(Base::class, $ciper);
        $this->assertSame('key', $ciper->key);
    }

    /**
     * Setup the logger, return the mock writer
     *
     * @return \Zend\Log\Writer\Mock
     */
    private function setupLogger()
    {
        $logWriter = new \Zend\Log\Writer\Mock();
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($logWriter);
        Logger::setLogger($logger);

        return $logWriter;
    }
}
