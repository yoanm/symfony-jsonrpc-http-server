<?php
namespace Tests\Common\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tests\Common\Mock\ConcreteResolver;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

abstract class AbstractTestClass extends AbstractExtensionTestCase
{
    // Public services
    const EXPECTED_ENDPOINT_SERVICE_ID = 'json_rpc_http_server.endpoint';
    const EXPECTED_METHOD_RESOLVER_SERVICE_ID = 'json_rpc_server_prs11_resolver.method';

    // Public tags

    const EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';

    //const EXPECTED_METHOD_MANAGER_SERVICE_ID = 'json_rpc_http_server.sdk.app.manager.method';
    const EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID = 'json_rpc_http_server.alias.method_resolver';

    const EXPECTED_HTTP_ENDPOINT_PATH_CONTAINER_PARAM = 'json_rpc_http_server.http_endpoint_path';

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new JsonRpcHttpServerExtension()
        ];
    }

    protected function load(array $configurationValues = [], $mockResolver = true, $compile = true)
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
     * @return Definition
     */
    protected function createJsonRpcMethodDefinition()
    {
        return (new Definition(\stdClass::class))
            ->setPrivate(false);
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
