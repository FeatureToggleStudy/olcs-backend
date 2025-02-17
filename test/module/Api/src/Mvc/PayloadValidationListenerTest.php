<?php

namespace Dvsa\OlcsTest\Api\Mvc;

use Dvsa\Olcs\Api\Mvc\PayloadValidationListener as Sut;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as TestQuery;
use Dvsa\Olcs\Api\Domain\Command\MyAccount\UpdateMyAccount as TestCommand;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * PayloadValidationListener Test
 */
class PayloadValidationListenerTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->annotationBuilder = m::mock(AnnotationBuilder::class);

        $this->sut = new Sut($this->annotationBuilder);
        parent::setUp();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')
            ->once()
            ->with(MvcEvent::EVENT_ROUTE, [$this->sut, 'onRoute'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnRouteNoRequest()
    {
        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn(null);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteNoRouteMatch()
    {
        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('GET');

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn(null);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteNoDtoClass()
    {
        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('GET');

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(null);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteGetValid()
    {
        $params = [];

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('GET')
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn([]);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestQuery::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestQuery::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->annotationBuilder->shouldReceive('createQuery')
            ->once()
            ->with(m::type(TestQuery::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteGetNotValid()
    {
        $params = [];

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('GET')
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn([]);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestQuery::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestQuery::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['ERROR']);

        $this->annotationBuilder->shouldReceive('createQuery')
            ->once()
            ->with(m::type(TestQuery::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_422, $result->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $result->getHeaders()->toArray());
        $this->assertEquals('["ERROR"]', $result->getContent());
    }

    public function testOnRoutePostJsonNotValid()
    {
        $params = [];
        $data = ['POST_DATA'];

        $mockContentType = m::mock();
        $mockContentType->shouldReceive('getMediaType')
            ->once()
            ->andReturn(Sut::JSON_MEDIA_TYPE);

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST')
            ->shouldReceive('getHeader')
            ->once()
            ->with('contenttype')
            ->andReturn($mockContentType)
            ->shouldReceive('getContent')
            ->once()
            ->andReturn(json_encode($data));

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestCommand::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestCommand::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(CommandInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['ERROR']);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with(m::type(TestCommand::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(Sut::JSON_NOT_VALID_CODE, $result->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $result->getHeaders()->toArray());
        $this->assertEquals('["ERROR"]', $result->getContent());
    }

    public function testOnRoutePostXmlNotValid()
    {
        $params = [];
        $data = '<xml><some_data>POST_DATA</some_data></xml>';

        $mockContentType = m::mock();
        $mockContentType->shouldReceive('getMediaType')
            ->once()
            ->andReturn('text/xml');

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST')
            ->shouldReceive('getHeader')
            ->once()
            ->with('contenttype')
            ->andReturn($mockContentType)
            ->shouldReceive('getContent')
            ->once()
            ->andReturn($data);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestCommand::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestCommand::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(CommandInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with(m::type(TestCommand::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(Sut::XML_NOT_VALID_CODE, $result->getStatusCode());
        $this->assertEquals([], $result->getHeaders()->toArray());
        $this->assertEquals('', $result->getContent());
    }

    public function testOnRoutePostDataNotValid()
    {
        $params = [];
        $data = ['data'];
        $files = ['field' => 'file'];

        $mockContentType = m::mock();
        $mockContentType->shouldReceive('getMediaType')
            ->once()
            ->andReturn('application/x-www-form-urlencoded');

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST')
            ->shouldReceive('getHeader')
            ->once()
            ->with('contenttype')
            ->andReturn($mockContentType)
            ->shouldReceive('getPost')
            ->once()
            ->andReturn($data)
            ->shouldReceive('getFiles')
            ->once()
            ->andReturn($files);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestCommand::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestCommand::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(CommandInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['ERROR']);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with(m::type(TestCommand::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(Sut::JSON_NOT_VALID_CODE, $result->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $result->getHeaders()->toArray());
        $this->assertEquals('["ERROR"]', $result->getContent());
    }
}
