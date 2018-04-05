<?php
namespace Tests\Common\Infra\Symfony\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

abstract class AbstractTestClass extends AbstractExtensionTestCase
{
    // Public services
    const EXPECTED_ENDPOINT_SERVICE_ID = 'yoanm.jsonrpc_http_server.endpoint';
    const EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID = 'yoanm.jsonrpc_http_server.resolver.service_name';

    // Public tags
    const EXPECTED_METHOD_RESOLVER_TAG = 'yoanm.jsonrpc_http_server.method_resolver';
    const EXPECTED_JSONRPC_METHOD_TAG = 'yoanm.jsonrpc_http_server.jsonrpc_method';

    const EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';

    const EXPECTED_METHOD_MANAGER_SERVICE_ID = 'yoanm.jsonrpc_http_server.sdk.app.manager.method';
    const EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID = 'yoanm.jsonrpc_http_server.infra.resolver.method';

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

        // And then compile container to have correct inject
        $this->compile();
    }


    protected function assertEndpointIsUsable()
    {
        // Retrieving this service will imply to load all related dependencies
        // Any binding issues will be raised
        $this->assertNotNull($this->container->get(self::EXPECTED_ENDPOINT_SERVICE_ID));
    }

    /**
     * @param $methodName
     * @return Definition
     */
    protected function createJsonRpcMethodDefinition($methodName)
    {
        $jsonRpcMethodServiceDefinition = new Definition(\stdClass::class);
        $jsonRpcMethodServiceDefinition
            ->setPrivate(false)
            ->addTag(
                self::EXPECTED_JSONRPC_METHOD_TAG,
                [self::EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY => $methodName]
            );

        return $jsonRpcMethodServiceDefinition;
    }

    /**
     * @return Definition
     */
    protected function createCustomMethodResolverDefinition()
    {
        $customResolverService = new Definition(CustomMethodResolverClass::class);
        $customResolverService->addTag(self::EXPECTED_METHOD_RESOLVER_TAG);

        return $customResolverService;
    }
}
