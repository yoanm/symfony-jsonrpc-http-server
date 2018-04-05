<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use DemoApp\AbstractKernel;
use DemoApp\DefaultKernel;
use DemoApp\KernelWithBundle;
use DemoApp\KernelWithBundleAndCustomLoader;
use DemoApp\KernelWithCustomResolver;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines application features from the specific context.
 */
class DemoAppContext implements Context
{
    /** @var Response|null */
    private $lastResponse;
    /** @var bool */
    private $useCustomResolver = false;
    /** @var bool */
    private $useBundle = false;

    /**
     * @Given I use my DemoApp custom method resolver
     */
    public function givenIUseMyDemoAppCustomMethodResolve()
    {
        $this->useCustomResolver = true;
    }

    /**
     * @Given DemoApp will use JsonRpcHttpServerBundle
     */
    public function givenDemoAppWillUseBundle()
    {
        $this->useBundle = true;
    }

    /**
     * @When I send following :httpMethod input on :uri demoApp kernel endpoint:
     */
    public function whenISendFollowingPayloadToDemoApp($httpMethod, $uri, PyStringNode $payload)
    {
        $this->lastResponse = null;

        $kernel = $this->getDemoAppKernel();
        $kernel->boot();
        $this->lastResponse = $kernel->handle(
            Request::create($uri, $httpMethod, [], [], [], [], $payload->getRaw())
        );
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
     * @return AbstractKernel
     */
    protected function getDemoAppKernel()
    {
        $env = 'prod';
        $debug = true;
        switch (true) {
            case true === $this->useBundle && true === $this->useCustomResolver:
                return new KernelWithBundleAndCustomLoader($env, $debug);
            case true === $this->useBundle && false === $this->useCustomResolver:
                return new KernelWithBundle($env, $debug);
            case false === $this->useBundle && true === $this->useCustomResolver:
                return new KernelWithCustomResolver($env, $debug);

        }

        return new DefaultKernel($env, $debug);
    }
}
