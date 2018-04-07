<?php
namespace Tests\Common\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

abstract class AbstractTestClass extends AbstractExtensionTestCase
{
    // Public services
    const EXPECTED_ENDPOINT_SERVICE_ID = 'json_rpc_http_server.endpoint';
    const EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID = 'json_rpc_http_server.resolver.service_name';

    // Public tags
    const EXPECTED_METHOD_RESOLVER_TAG = 'json_rpc_http_server.method_resolver';
    const EXPECTED_JSONRPC_METHOD_TAG = 'json_rpc_http_server.jsonrpc_method';

    const EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';

    const EXPECTED_METHOD_MANAGER_SERVICE_ID = 'json_rpc_http_server.sdk.app.manager.method';
    const EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID = 'json_rpc_http_server.infra.resolver.method';

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

    protected function load(array $configurationValues = [])
    {
        parent::load($configurationValues);

        // And then compile container to have correct injection
        $this->compile();
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
     * @return Definition
     */
    protected function createCustomMethodResolverDefinition()
    {
        $customResolverService = new Definition($this->prophesize(MethodResolverInterface::class)->reveal());
        $customResolverService->addTag(self::EXPECTED_METHOD_RESOLVER_TAG);

        return $customResolverService;
    }
}
