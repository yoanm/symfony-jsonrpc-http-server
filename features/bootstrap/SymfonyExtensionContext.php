<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use DemoApp\Method\MethodB;
use DemoApp\Resolver\JsonRpcMethodResolver;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;

/**
 * Defines application features from the specific context.
 */
class SymfonyExtensionContext implements Context
{
    const CUSTOM_METHOD_RESOLVER_SERVICE_ID = 'custom-method-resolver-service';

    /** @var JsonRpcHttpServerExtension */
    private $extension;
    /** @var Prophet */
    private $prophet;
    /** @var ContainerBuilder */
    private $containerBuilder;
    /** @var mixed */
    private $endpoint;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->prophet = new Prophet();
        $this->extension = new JsonRpcHttpServerExtension();
    }

    /**
     * @Given I process the symfony extension
     */
    public function givenIProcessTheSymfonyExtension()
    {
        $this->extension->load([], $this->getContainerBuilder());
    }

    /**
     * @Given there is a public :serviceName JSON-RPC method service
     */
    public function givenThereAJsonRpcMethodService($serviceId)
    {
        $this->getContainerBuilder()->setDefinition($serviceId, $this->createJsonRpcMethodDefinition());
    }

    /**
     * @Given I inject my :methodName to :serviceName JSON-RPC mapping into default method resolver definition
     */
    public function givenIInjectMyJsonRpcMethodIntoDefaultMethodResolverDefinition($methodName, $serviceName)
    {
        $this->injectJsonRpcMethodToDefaultResolverService($methodName, $serviceName, true);
    }

    /**
     * @Given I inject my :methodName to :serviceName JSON-RPC mapping into default method resolver instance
     */
    public function givenIInjectMyJsonRpcMethodIntoDefaultMethodResolverInstance($methodName, $serviceName)
    {
        $this->injectJsonRpcMethodToDefaultResolverService($methodName, $serviceName);
    }

    /**
     * @Given I tag my custom method resolver service with :tagName
     */
    public function givenITagMyCustomMethodResolverServiceWith($tagName)
    {
        $this->getContainerBuilder()->findDefinition(self::CUSTOM_METHOD_RESOLVER_SERVICE_ID)->addTag($tagName);
    }

    /**
     * @Given I inject my :methodName JSON-RPC method into my custom method resolver instance
     */
    public function givenIInjectMyJsonRpcMethodIntoMyCustomMethodResolverInstance($methodName)
    {
        $this->injectJsonRpcMethodToCustomResolverService($methodName, $this->createJsonRpcMethod());
    }

    /**
     * @Given I inject my :methodName JSON-RPC method into my custom method resolver definition
     */
    public function givenIInjectMyJsonRpcMethodIntoMyCustomMethodResolverDefinition($methodName)
    {
        $this->injectJsonRpcMethodToCustomResolverService($methodName, $this->createJsonRpcMethodDefinition());
    }

    /**
     * @Given I have a JSON-RPC method service definition with :tagName tag and following tag attributes:
     */
    public function givenITagMyJsonRpcMethodServiceWithTagAndFollowingAttributes(
        $tagName,
        PyStringNode $tagAttributeNode
    ) {
        $definition = $this->createJsonRpcMethodDefinition()
            ->addTag($tagName, json_decode($tagAttributeNode, true));
        $this->getContainerBuilder()->setDefinition(uniqid(), $definition);
    }

    /**
     * @When I load endpoint from :serviceId service
     */
    public function whenILoadEndpointFromService($serviceId)
    {
        $this->extension->process($this->getContainerBuilder());
        $this->getContainerBuilder()->compile();
        $this->endpoint = $this->getContainerBuilder()->get($serviceId);
    }

    /**
     * @Then endpoint should respond to following JSON-RPC methods:
     */
    public function thenEndpointShouldResponseToFollowingJsonRpcMethods(TableNode $methodList)
    {
        Assert::assertInstanceOf(JsonRpcHttpEndpoint::class, $this->endpoint);
        $methodList = array_map('array_shift', $methodList->getRows());
        $this->assertEndpointRespondToCalls($this->endpoint, $methodList);
    }

    /**
     * @param JsonRpcEndpoint $endpoint
     * @param array           $methodNameList
     */
    private function assertEndpointRespondToCalls(JsonRpcHttpEndpoint $endpoint, array $methodNameList)
    {
        foreach ($methodNameList as $methodName) {
            $requestId = uniqid();
            $requestContent = json_encode(
                [
                    'jsonrpc' => '2.0',
                    'id' => $requestId,
                    'method' => $methodName
                ]
            );
            $request = new Request([], [], [], [], [], [], $requestContent);
            $request->setMethod(Request::METHOD_POST);
            Assert::assertSame(
                json_encode(
                    [
                        'jsonrpc' => '2.0',
                        'id' => $requestId,
                        'result' => 'MethodB'
                    ]
                ),
                $endpoint->index($request)->getContent()
            );
        }
    }

    /**
     * @return JsonRpcMethodInterface
     */
    private function createJsonRpcMethod()
    {
        return new MethodB();
    }

    /**
     * @return Definition
     */
    private function createJsonRpcMethodDefinition()
    {
        return (new Definition(MethodB::class))->setPrivate(false);
    }

    /**
     * @param string     $methodName
     * @param string     $methodServiceId
     * @param bool|false $isDefinition
     */
    private function injectJsonRpcMethodToDefaultResolverService($methodName, $methodServiceId, $isDefinition = false)
    {
        $resolverServiceId = JsonRpcHttpServerExtension::SERVICE_NAME_RESOLVER_SERVICE_NAME;
        if (true === $isDefinition) {
            $this->getContainerBuilder()
                ->getDefinition($resolverServiceId)
                ->addMethodCall('addMethodMapping', [$methodName, $methodServiceId]);
        } else {
            $this->getContainerBuilder()
                ->get($resolverServiceId)
                ->addMethodMapping($methodName, $methodServiceId);
        }
    }

    /**
     * @param string                            $methodName
     * @param JsonRpcMethodInterface|Definition $method
     */
    private function injectJsonRpcMethodToCustomResolverService($methodName, $method)
    {
        $resolverServiceId = self::CUSTOM_METHOD_RESOLVER_SERVICE_ID;
        if ($method instanceof Definition) {
            $this->getContainerBuilder()
                ->getDefinition($resolverServiceId)
                ->addMethodCall('addMethod', [$method, $methodName]);
        } else {
            $this->getContainerBuilder()
                ->get($resolverServiceId)
                ->addMethod($method, $methodName);
        }
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainerBuilder()
    {
        if (!$this->containerBuilder) {
            $this->containerBuilder = new ContainerBuilder();
            // Add definition of custom resolver (without tags)
            $customResolverDefinition = (new Definition(JsonRpcMethodResolver::class))->setPublic(true);
            $this->containerBuilder->setDefinition(self::CUSTOM_METHOD_RESOLVER_SERVICE_ID, $customResolverDefinition);
        }
        return $this->containerBuilder;
    }
}
