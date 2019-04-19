<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use DemoApp\AbstractKernel;
use DemoApp\DefaultKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AbstractContext implements Context
{
    public function jsonDecode($encodedData)
    {
        $decoded = json_decode($encodedData, true);

        if (JSON_ERROR_NONE != json_last_error()) {
            throw new \Exception(
                json_last_error_msg(),
                json_last_error()
            );
        }

        return $decoded;
    }

    /**
     * @param string $uri
     * @param string $httpMethod
     * @param string $content
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function sendHttpContentTo(string $uri, string $httpMethod, string $content): Response
    {
        $kernel = $this->getDemoAppKernel();
        $kernel->boot();
        $request = Request::create($uri, $httpMethod, [], [], [], [], $content);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        $kernel->shutdown();

        return $response;
    }

    /**
     * @return AbstractKernel
     */
    public function getDemoAppKernel()
    {
        $env = 'prod';
        $debug = true;

        return new DefaultKernel($env, $debug);
    }
}
