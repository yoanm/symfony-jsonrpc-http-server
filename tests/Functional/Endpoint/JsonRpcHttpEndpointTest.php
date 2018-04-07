<?php
namespace Tests\Functional\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint as SDKJsonRpcEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint
 */
class JsonRpcHttpEndpointTest extends TestCase
{
    /** @var JsonRpcHttpEndpoint */
    private $endpoint;

    /** @var SdkJsonRpcEndpoint|ObjectProphecy */
    private $sdkEndpoint;

    protected function setUp()
    {
        $this->sdkEndpoint = $this->prophesize(SdkJsonRpcEndpoint::class);

        $this->endpoint = new JsonRpcHttpEndpoint(
            $this->sdkEndpoint->reveal()
        );
    }

    public function testShouldHandleRequestContentAndReturnA200ResponseContainingSDKEndpointReturnedValue()
    {
        $requestContent = 'request-content';
        $expextedResponseContent = 'expected-response-content';

        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);

        $request->getMethod()
            ->willReturn(Request::METHOD_POST)
            ->shouldBeCalled();

        $request->getContent()
            ->willReturn($requestContent)
            ->shouldBeCalled();

        $this->sdkEndpoint->index($requestContent)
            ->willReturn($expextedResponseContent)
            ->shouldBeCalled();

        $response = $this->endpoint->index($request->reveal());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($expextedResponseContent, $response->getContent());
    }

    public function testShouldCheckIfRequestUsePostMethodAndReturnErrorResponseIfNot()
    {
        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);

        $request->getMethod()
            ->willReturn(Request::METHOD_GET)
            ->shouldBeCalled();

        $response = $this->endpoint->index($request->reveal());

        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertSame('A JSON-RPC HTTP call must use POST', $response->getContent());
    }
}
