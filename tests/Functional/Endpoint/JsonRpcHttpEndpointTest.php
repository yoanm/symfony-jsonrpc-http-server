<?php
namespace Tests\Functional\Endpoint;

use PHPUnit\Framework\TestCase;
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

    public function testHttPostShouldHandleRequestContentAndReturnA200ResponseContainingSDKEndpointReturnedValue()
    {
        $requestContent = 'request-content';
        $expectedResponseContent = 'expected-response-content';

        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);

        $request->getContent()
            ->willReturn($requestContent)
            ->shouldBeCalled();

        $this->sdkEndpoint->index($requestContent)
            ->willReturn($expectedResponseContent)
            ->shouldBeCalled();

        $response = $this->endpoint->httpPost($request->reveal());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testHttOptionsShouldReturnAllowedMethodsAndContentType()
    {
        $expectedAllowedMethodList = [Request::METHOD_POST, Request::METHOD_OPTIONS];
        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);

        $response = $this->endpoint->httpOptions($request->reveal());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        // Check allowed methods
        $this->assertSame($expectedAllowedMethodList, $response->headers->get('Allow', null, false));
        $this->assertSame(
            $expectedAllowedMethodList,
            $response->headers->get('Access-Control-Request-Method', null, false)
        );

        // Check allowed content types
        $this->assertSame('application/json', $response->headers->get('Accept'));
        $this->assertSame('Content-Type', $response->headers->get('Access-Control-Allow-Headers'));
    }
}
