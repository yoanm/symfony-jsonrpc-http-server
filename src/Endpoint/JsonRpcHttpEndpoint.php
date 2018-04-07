<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Endpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint as SDKJsonRpcEndpoint;

/**
 * Class JsonRpcHttpEndpoint
 */
class JsonRpcHttpEndpoint
{
    /** @var SdkJsonRpcEndpoint */
    private $sdkEndpoint;

    /**
     * @param SDKJsonRpcEndpoint $sdkEndpoint
     */
    public function __construct(SDKJsonRpcEndpoint $sdkEndpoint)
    {
        $this->sdkEndpoint = $sdkEndpoint;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request) : Response
    {
        if (Request::METHOD_POST !== $request->getMethod()) {
            return new Response('A JSON-RPC HTTP call must use POST', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        return new Response(
            $this->sdkEndpoint->index($request->getContent())
        );
    }
}
