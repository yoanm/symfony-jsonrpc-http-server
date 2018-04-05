<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use DemoApp\Kernel;
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

    /**
     * @Given I use my DemoApp custom method resolver
     */
    public function givenIUseMyDemoAppCustomMethodResolve()
    {
        $this->useCustomResolver = true;
    }

    /**
     * @When I send following :httpMethod input on demoApp kernel:
     */
    public function whenISendFollowingPayloadToDemoApp($httpMethod, PyStringNode $payload)
    {
        $this->lastResponse = null;

        $kernel = $this->getDemoAppKernel();
        $kernel->boot();
        $this->lastResponse = $kernel->handle(
            Request::create('/my-json-rpc-endpoint', $httpMethod, [], [], [], [], $payload->getRaw())
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
     * @return Kernel
     */
    protected function getDemoAppKernel()
    {
        if (true === $this->useCustomResolver) {
            return new KernelWithCustomResolver('prod', true);
        }

        return new Kernel('prod', true);
    }
}
