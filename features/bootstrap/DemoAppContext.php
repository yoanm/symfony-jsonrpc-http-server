<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use DemoApp\AbstractKernel;
use DemoApp\DefaultKernel;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

/**
 * Defines application features from the specific context.
 */
class DemoAppContext implements Context
{
    /** @var Response|null */
    private $lastResponse;

    /**
     * @When I send following :httpMethod input on :uri demoApp kernel endpoint:
     */
    public function whenISendFollowingPayloadToDemoApp($httpMethod, $uri, PyStringNode $payload)
    {
        $this->lastResponse = null;

        $kernel = $this->getDemoAppKernel();
        $kernel->boot();
        $request = Request::create($uri, $httpMethod, [], [], [], [], $payload->getRaw());
        $this->lastResponse = $kernel->handle($request);
        $kernel->terminate($request, $this->lastResponse);
        $kernel->shutdown();
    }

    /**
     * @Then I should have a :httpCode response from demoApp with following content:
     */
    public function thenIShouldHaveAResponseFromDemoAppWithFollowingContent($httpCode, PyStringNode $payload)
    {
        Assert::assertInstanceOf(Response::class, $this->lastResponse);
        // Decode payload to get ride of indentation, spacing, etc
        Assert::assertEquals(
            json_decode($payload->getRaw(), true),
            json_decode($this->lastResponse->getContent(), true)
        );
        Assert::assertSame((int) $httpCode, $this->lastResponse->getStatusCode());
    }

    /**
     * @Then Collector should have :methodClass JSON-RPC method with name :methodName
     */
    public function thenCollectorShouldHaveAMethodWithName($methodClass, $methodName)
    {
        $kernel = $this->getDemoAppKernel();
        $kernel->boot();
        $methodInstance = $kernel->getContainer()
            ->get('mapping_aware_service')
            ->resolve($methodName)
        ;
        $kernel->shutdown();

        Assert::assertInstanceOf(
            JsonRpcMethodInterface::class,
            $methodInstance,
            'Method must be a JsonRpcMethodInterface instance'
        );
        Assert::assertInstanceOf(
            $methodClass,
            $methodInstance,
            sprintf('Method "%s" is not an instance of "%s"', $methodName, $methodClass)
        );
    }

    /**
     * @return AbstractKernel
     */
    protected function getDemoAppKernel()
    {
        $env = 'prod';
        $debug = true;

        return new DefaultKernel($env, $debug);
    }
}
