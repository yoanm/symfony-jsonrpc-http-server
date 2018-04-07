<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\LogicException;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionWithConfigParsedTest extends AbstractTestClass
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new JsonRpcHttpServerExtension(true)
        ];
    }

    public function testShouldManageCustomEndpointPathFromConfiguration()
    {
        $myCustomEndpoint = 'my-custom-endpoint';
        $this->load(['http_endpoint_path' => $myCustomEndpoint]);

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasParameter(self::EXPECTED_HTTP_ENDPOINT_PATH_CONTAINER_PARAM, $myCustomEndpoint);

        $this->assertEndpointIsUsable();
    }

    public function testShouldManageCustomResolverFromConfiguration()
    {
        $myCustomResolverServiceId = 'my-custom-resolver';
        $this->setDefinition($myCustomResolverServiceId, $this->createCustomMethodResolverDefinition());

        $this->load(['method_resolver' => $myCustomResolverServiceId]);

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasAlias(
            self::EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID,
            $myCustomResolverServiceId
        );

        $this->assertEndpointIsUsable();
    }

    public function testShouldManageMethodsMapping()
    {
        $serviceA = uniqid();
        $serviceB = uniqid();
        $serviceC = uniqid();
        $methodAName = 'method-a';
        $methodAAlias1 = 'method-a-alias-1';
        $methodAAlias2 = 'method-a-alias-2';
        $methodBName = 'method-b';
        $methodBAlias = 'method-b-alias';
        $methodCName = 'method-c';
        $this->setDefinition($serviceA, $this->createJsonRpcMethodDefinition());
        $this->setDefinition($serviceB, $this->createJsonRpcMethodDefinition());
        $this->setDefinition($serviceC, $this->createJsonRpcMethodDefinition());

        $this->load([
            'methods_mapping' => [
                $methodAName => [
                    'service' => $serviceA,
                    'aliases' => [$methodAAlias1, $methodAAlias2]
                ],
                $methodBName => [
                    'service' => $serviceB,
                    'aliases' => $methodBAlias,
                ],
                $methodCName => $serviceC
            ]
        ]);

        // Assert each methods have been mapped correctly

        // MethodA and aliases
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [$methodAName, $serviceA]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [$methodAAlias1, $serviceA]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [$methodAAlias2, $serviceA]
        );
        // MethodB and alias
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [$methodBName, $serviceB]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [$methodBAlias, $serviceB]
        );
        // MethodC
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_SERVICE_NAME_RESOLVER_SERVICE_ID,
            'addMethodMapping',
            [$methodCName, $serviceC]
        );

        $this->assertJsonRpcMethodServiceIsAvailable($serviceA);
        $this->assertJsonRpcMethodServiceIsAvailable($serviceB);
        $this->assertJsonRpcMethodServiceIsAvailable($serviceC);

        $this->assertEndpointIsUsable();
    }

    public function testShouldThrowAnExceptionIfJsonRpcMethodUsedWithTagIsNotPublic()
    {
        $jsonRpcMethodServiceId = uniqid();

        $methodService = $this->createJsonRpcMethodDefinition()->setPublic(false);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);


        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            sprintf(
                'Service "%s" is taggued as JSON-RPC method but is not public. '
                .'Service must be public in order to retrieve it later',
                $jsonRpcMethodServiceId
            )
        );

        $this->load([
            'methods_mapping' => [
                'a-method' => $jsonRpcMethodServiceId
            ]
        ]);
    }
}
