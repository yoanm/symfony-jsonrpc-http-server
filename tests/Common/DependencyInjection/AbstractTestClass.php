<?php
namespace Tests\Common\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tests\Common\Mock\ConcreteJsonRpcMethod;
use Tests\Common\Mock\ConcreteResolver;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

abstract class AbstractTestClass extends AbstractExtensionTestCase
{
    const EXPECTED_ENDPOINT_SERVICE_ID = 'json_rpc_http_server.endpoint';
    const EXPECTED_HTTP_ENDPOINT_PATH_CONTAINER_PARAM = 'json_rpc_http_server.http_endpoint_path';
    const EXPECTED_PARAMS_VALIDATOR_ALIAS = 'json_rpc_http_server.alias.params_validator';
    const EXPECTED_REQUEST_HANDLER_SERVICE_ID = 'json_rpc_server_sdk.app.handler.jsonrpc_request';

    const EXPECTED_JSONRPC_METHOD_TAG = 'json_rpc_http_server.jsonrpc_method';
    const EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        return [
            new JsonRpcHttpServerExtension()
        ];
    }

    protected function loadContainer(array $configurationValues = [], $mockResolver = true, $compile = true): void
    {
        // Inject event dispatcher
        $this->setDefinition('event_dispatcher', new Definition(EventDispatcher::class));

        if (true == $mockResolver) {
            $this->mockResolver();
        }

        parent::load($configurationValues);

        if (true === $compile) {
            // And then compile container to have correct injection
            $this->compile();
        }
    }


    protected function assertEndpointIsUsable()
    {
        // Retrieving this service will imply to load all related dependencies
        // Any binding issues will be raised
        $this->assertNotNull($this->container->get(self::EXPECTED_ENDPOINT_SERVICE_ID));
    }

    /**
     * @param $jsonRpcMethodServiceId
     */
    protected function assertJsonRpcMethodServiceIsAvailable($jsonRpcMethodServiceId)
    {
        $this->assertNotNull($this->container->get($jsonRpcMethodServiceId));
    }



    /**
     * @param Definition $definition
     * @param string     $methodName
     */
    protected function addJsonRpcMethodTag(Definition $definition, $methodName)
    {
        $definition->addTag(
            self::EXPECTED_JSONRPC_METHOD_TAG,
            [self::EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY => $methodName]
        );
    }

    /**
     * @param string $class
     *
     * @return Definition
     */
    protected function createJsonRpcMethodDefinition($class = ConcreteJsonRpcMethod::class)
    {
        return (new Definition($class))
            ->setPublic(true);
    }

    protected function mockResolver()
    {
        $this->setDefinition(
            'json_rpc_http_server.alias.method_resolver',
            new Definition(ConcreteResolver::class)
        );
    }

    /**
     * @return Definition
     */
    protected function createCustomMethodResolverDefinition()
    {
        return new Definition($this->prophesize(JsonRpcMethodResolverInterface::class)->reveal());
    }
}
