<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Infra\Endpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint as SDKJsonRpcEndpoint;

class JsonRpcEndpoint
{
    /** @var SdkJsonRpcEndpoint */
    private $sdkEndpoint;

    public function __construct(SDKJsonRpcEndpoint $sdkEndpoint)
    {
        $this->sdkEndpoint = $sdkEndpoint;
    }

    public function index(Request $request) : Response
    {
        return new Response(
            $this->sdkEndpoint->index($request->getContent())
        );
    }
}
