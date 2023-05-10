<?php
namespace Tests\Functional\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint as SDKJsonRpcEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\EventListener\RequestListener;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\EventListener\RequestListener
 */
class RequestListenerTest extends TestCase
{
    /** @var RequestListener */
    private $endpoint;

    /** @var SdkJsonRpcEndpoint|ObjectProphecy */
    private $sdkEndpoint;
    /** @var string */
    private $uri = '/my-uri';


    protected function setUp(): void
    {
        $this->sdkEndpoint = $this->prophesize(SdkJsonRpcEndpoint::class);

        $this->endpoint = new RequestListener(
            $this->sdkEndpoint->reveal(),
            $this->uri
        );
    }

    public function testHttPostShouldHandleRequestContentAndReturnA200ResponseContainingSDKEndpointReturnedValue()
    {
        $requestContent = 'request-content';
        $expectedResponseContent = 'expected-response-content';
        /** @var null|Request $actualResponse */
        $actualResponse = null;

        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        /** @var RequestEvent|ObjectProphecy $requestEvent */
        $requestEvent = $this->prophesize(RequestEvent::class);
        $requestEvent->isMasterRequest()
            ->willReturn(true)
            ->shouldBeCalled()
        ;
        $requestEvent->getRequest()
            ->willReturn($request->reveal())
            ->shouldBeCalled()
        ;

        $request->getContent()
            ->willReturn($requestContent)
            ->shouldBeCalled()
        ;
        $request->getRequestUri()
            ->willReturn($this->uri)
            ->shouldBeCalled()
        ;
        $request->getMethod()
            ->willReturn(Request::METHOD_POST)
            ->shouldBeCalled()
        ;

        $this->sdkEndpoint->index($requestContent)
            ->willReturn($expectedResponseContent)
            ->shouldBeCalled();

        $requestEvent->setResponse(Argument::type(Response::class))
            ->will(function ($args) use (&$actualResponse) {
                $actualResponse = $args[0];
            })
            ->shouldBeCalled()
        ;

        $this->endpoint->onKernelRequest($requestEvent->reveal());

        $this->assertInstanceOf(Response::class, $actualResponse);
        $this->assertSame(Response::HTTP_OK, $actualResponse->getStatusCode());
        $this->assertSame($expectedResponseContent, $actualResponse->getContent());
        $this->assertSame('application/json', $actualResponse->headers->get('Content-Type'));
    }

    public function testHttOptionsShouldReturnAllowedMethodsAndContentType()
    {
        $expectedAllowedMethodList = implode(', ', [Request::METHOD_POST, Request::METHOD_OPTIONS]);
        /** @var null|Request $actualResponse */
        $actualResponse = null;

        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        /** @var RequestEvent|ObjectProphecy $requestEvent */
        $requestEvent = $this->prophesize(RequestEvent::class);
        $requestEvent->isMasterRequest()
            ->willReturn(true)
            ->shouldBeCalled()
        ;
        $requestEvent->getRequest()
            ->willReturn($request->reveal())
            ->shouldBeCalled()
        ;
        $request->getRequestUri()
            ->willReturn($this->uri)
            ->shouldBeCalled()
        ;
        $request->getMethod()
            ->willReturn(Request::METHOD_OPTIONS)
            ->shouldBeCalled()
        ;

        $requestEvent->setResponse(Argument::type(Response::class))
            ->will(function ($args) use (&$actualResponse) {
                $actualResponse = $args[0];
            })
            ->shouldBeCalled()
        ;

        $this->endpoint->onKernelRequest($requestEvent->reveal());

        $this->assertInstanceOf(Response::class, $actualResponse);
        $this->assertSame(Response::HTTP_OK, $actualResponse->getStatusCode());
        $this->assertSame('application/json', $actualResponse->headers->get('Content-Type'));
        // Check allowed methods
        $this->assertSame($expectedAllowedMethodList, $actualResponse->headers->get('Allow', null));
        $this->assertSame(
            $expectedAllowedMethodList,
            $actualResponse->headers->get('Access-Control-Request-Method', null)
        );
        // Check allowed content types
        $this->assertSame('application/json', $actualResponse->headers->get('Accept'));
        $this->assertSame('Content-Type', $actualResponse->headers->get('Access-Control-Allow-Headers'));
    }

    public function testDoNohingIfNotTheMasterRequest()
    {
        /** @var RequestEvent|ObjectProphecy $requestEvent */
        $requestEvent = $this->prophesize(RequestEvent::class);
        $requestEvent->isMasterRequest()
            ->willReturn(false)
            ->shouldBeCalled()
        ;

        $this->endpoint->onKernelRequest($requestEvent->reveal());
    }

    public function testDoNohingIfNotTheRightUri()
    {
        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        /** @var RequestEvent|ObjectProphecy $requestEvent */
        $requestEvent = $this->prophesize(RequestEvent::class);
        $requestEvent->isMasterRequest()
            ->willReturn(true)
            ->shouldBeCalled()
        ;
        $requestEvent->getRequest()
            ->willReturn($request->reveal())
            ->shouldBeCalled()
        ;
        $request->getRequestUri()
            ->willReturn('/another-uri')
            ->shouldBeCalled()
        ;

        $this->endpoint->onKernelRequest($requestEvent->reveal());
    }



    public function testDoNohingIfHttpMethodNotManaged()
    {
        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        /** @var RequestEvent|ObjectProphecy $requestEvent */
        $requestEvent = $this->prophesize(RequestEvent::class);
        $requestEvent->isMasterRequest()
            ->willReturn(true)
            ->shouldBeCalled()
        ;
        $requestEvent->getRequest()
            ->willReturn($request->reveal())
            ->shouldBeCalled()
        ;
        $request->getRequestUri()
            ->willReturn($this->uri)
            ->shouldBeCalled()
        ;

        $request->getMethod()
            ->willReturn(Request::METHOD_GET)
            ->shouldBeCalled()
        ;

        $this->endpoint->onKernelRequest($requestEvent->reveal());
    }
}
