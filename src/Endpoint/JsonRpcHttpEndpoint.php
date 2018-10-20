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

    /** @var string[] */
    private $allowedMethodList = [];

    /**
     * @param SDKJsonRpcEndpoint $sdkEndpoint
     */
    public function __construct(SDKJsonRpcEndpoint $sdkEndpoint)
    {
        $this->sdkEndpoint = $sdkEndpoint;
        $this->allowedMethodList = [Request::METHOD_POST, Request::METHOD_OPTIONS];
    }

    /**
     * @return Response
     */
    public function httpOptions() : Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // Set allowed http methods
        $response->headers->set('Allow', $this->allowedMethodList);
        $response->headers->set('Access-Control-Request-Method', $this->allowedMethodList);

        // Set allowed content type
        $response->headers->set('Accept', 'application/json');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function httpPost(Request $request) : Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response->setContent(
            $this->sdkEndpoint->index(
                $request->getContent()
            )
        );

        return $response;
    }
}
