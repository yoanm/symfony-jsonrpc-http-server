<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Resolver\ServiceNameResolver;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionTest extends AbstractTestClass
{
    public function testShouldExposeEndpointService()
    {
        $this->load();

        $this->assertContainerBuilderHasService(self::EXPECTED_ENDPOINT_SERVICE_ID, JsonRpcHttpEndpoint::class);

        // Check that service is accessible through the container
        $this->assertNotNull($this->container->get(self::EXPECTED_ENDPOINT_SERVICE_ID));

        $this->assertEndpointIsUsable();
    }

    public function testShouldReturnAnXsdValidationBasePath()
    {
        $this->assertNotNull((new JsonRpcHttpServerExtension())->getXsdValidationBasePath());
    }

    public function testShouldExposeServiceNameResolverService()
    {
        $this->load();

        $this->assertContainerBuilderHasService(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            ServiceNameResolver::class
        );

        // Check that service is accessible through the container
        $this->assertNotNull($this->container->get(self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID));

        $this->assertEndpointIsUsable();
    }

    public function testShouldAliasPSR11MethodResolverByDefault()
    {
        $this->load();

        // Assert that MethodManager have the stub resolver
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            self::EXPECTED_METHOD_MANAGER_SERVICE_ID,
            0,
            new Reference(self::EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID)
        );

        // Assert PSR-11 resolver is an alias of the stub
        $this->assertContainerBuilderHasAlias(
            self::EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID,
            'json_rpc_http_server.psr11.infra.resolver.method'
        );

        $this->assertEndpointIsUsable();
    }

    public function testShouldAliasMethodResolverInjectionFoundByTag()
    {
        $myCustomResolverServiceId = 'my_custom_resolver';

        $this->setDefinition($myCustomResolverServiceId, $this->createCustomMethodResolverDefinition());

        $this->load();

        // Assert that MethodManager have the stub resolver
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            self::EXPECTED_METHOD_MANAGER_SERVICE_ID,
            0,
            new Reference(self::EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID)
        );

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasAlias(
            self::EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID,
            $myCustomResolverServiceId
        );

        $this->assertEndpointIsUsable();
    }

    public function testHandleManageJsonRpcMethodTag()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';
        $methodName2 = 'my-method-name-2';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method
        $methodService2 = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService2, $methodName2);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        $this->load();

        // Assert that method mapping have been correctly injected
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [
                $methodName,
                $jsonRpcMethodServiceId
            ],
            0
        );
        // Assert that method mapping have been correctly injected
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [
                $methodName2,
                $jsonRpcMethodServiceId2
            ],
            1
        );

        $this->assertEndpointIsUsable();
    }

    public function testHandleNotManageJsonRpcMethodTagIfCustomResolverIsUsed()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';
        $methodName2 = 'my-method-name-2';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method
        $methodService2 = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService2, $methodName2);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        // Add the custom method resolver
        $this->setDefinition(uniqid(), $this->createCustomMethodResolverDefinition());

        $this->load();

        // Assert that no method mapping have been added
        $this->assertEmpty(
            $this->container->getDefinition(self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID)->getMethodCalls()
        );

        $this->assertEndpointIsUsable();
    }

    public function testShouldThrowAnExceptionIfJsonRpcMethodUsedWithTagIsDoesNotHaveTheMethodTagAttribute()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method with empty tag attribute
        $methodService2 = $this->createJsonRpcMethodDefinition();
        $methodService2->addTag(self::EXPECTED_JSONRPC_METHOD_TAG);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            sprintf(
                'Service "%s" is taggued as JSON-RPC method but does not have'
                . ' method name defined under "%s" tag attribute key',
                $jsonRpcMethodServiceId2,
                self::EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY
            )
        );

        $this->load();
    }

    public function testShouldThrowAnExceptionIfJsonRpcMethodUsedWithTagIsNotPublic()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';
        $methodName2 = 'my-method-name-2';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method with private service
        $methodService2 = $this->createJsonRpcMethodDefinition()->setPublic(false);
        $this->addJsonRpcMethodTag($methodService2, $methodName2);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            sprintf(
                'Service "%s" is taggued as JSON-RPC method but is not public. '
                .'Service must be public in order to retrieve it later',
                $jsonRpcMethodServiceId2
            )
        );

        $this->load();
    }
}
