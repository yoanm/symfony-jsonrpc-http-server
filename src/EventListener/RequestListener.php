<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint as SDKJsonRpcEndpoint;

class RequestListener
{
    /** @var string */
    private $uri;
    /** @var SdkJsonRpcEndpoint */
    private $sdkEndpoint;
    /** @var string[] */
    private $allowedMethodList = [];

    public function __construct(SDKJsonRpcEndpoint $sdkEndpoint, $uri)
    {
        $this->uri = $uri;
        $this->sdkEndpoint = $sdkEndpoint;
        $this->allowedMethodList = [Request::METHOD_POST, Request::METHOD_OPTIONS];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // Don't do anything if it's not the master request !
            return;
        }

        $request = $event->getRequest();
        if ($this->uri === $request->getRequestUri()) {
            switch ($request->getMethod()) {
                case Request::METHOD_POST:
                    $event->setResponse($this->httpPost($request));
                    break;
                case Request::METHOD_OPTIONS:
                    $event->setResponse($this->httpOptions());
                    break;
            }
        }
    }

    protected function httpOptions() : Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // Set allowed http methods
        $allowedMethodListString = implode(', ', $this->allowedMethodList);
        $response->headers->set('Allow', $allowedMethodListString);
        $response->headers->set('Access-Control-Request-Method', $allowedMethodListString);

        // Set allowed content type
        $response->headers->set('Accept', 'application/json');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        return $response;
    }

    protected function httpPost(Request $request) : Response
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
