<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Tests\Common\DependencyInjection\ConcreteJsonRpcServerDispatcherAware;
use Tests\Common\Mock\ConcreteParamsValidator;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodAwareInterface;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionTest extends AbstractTestClass
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        return [
            new JsonRpcHttpServerExtension()
        ];
    }


    public function testShouldBeLoadable()
    {
        $this->loadContainer();

        $this->assertEndpointIsUsable();
    }

    public function testShouldManageCustomEndpointPathFromConfiguration()
    {
        $myCustomEndpoint = 'my-custom-endpoint';
        $this->loadContainer(['endpoint' => $myCustomEndpoint]);

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasParameter(self::EXPECTED_HTTP_ENDPOINT_PATH_CONTAINER_PARAM, $myCustomEndpoint);

        $this->assertEndpointIsUsable();
    }

    public function testShouldReturnAnXsdValidationBasePath()
    {
        $this->assertNotNull((new JsonRpcHttpServerExtension())->getXsdValidationBasePath());
    }

    public function testShouldBindServerDispatcherToDispatcherAwareService()
    {
        $dispatcherAwareServiceDefinition = new Definition(ConcreteJsonRpcServerDispatcherAware::class);
        $dispatcherAwareServiceDefinition->addTag('json_rpc_http_server.server_dispatcher_aware');

        $this->setDefinition('my-dispatcher-aware-service', $dispatcherAwareServiceDefinition);

        $this->loadContainer();

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'my-dispatcher-aware-service',
            'setJsonRpcServerDispatcher',
            [new Reference('json_rpc_http_server.dispatcher.server')],
            0
        );

        $this->assertEndpointIsUsable();
    }

    public function testShouldThrowAnExceptionIfDispatcherAwareServiceDoesNotUseRightTrait()
    {
        $dispatcherAwareServiceDefinition = new Definition(\stdClass::class);
        $dispatcherAwareServiceDefinition->addTag('json_rpc_http_server.server_dispatcher_aware');

        $this->setDefinition('my-dispatcher-aware-service', $dispatcherAwareServiceDefinition);

        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            'Service "my-dispatcher-aware-service" is taggued with '
                .'"json_rpc_http_server.server_dispatcher_aware" but does not use '
                .'"'.JsonRpcServerDispatcherAwareTrait::class.'"'
        );

        $this->loadContainer();
    }

    public function testShouldInjectParamsValidatorAliasIfDefined()
    {
        $myValidatorServiceId = 'my-params-validator-service';
        $paramsValidator = new Definition(ConcreteParamsValidator::class);

        $this->setDefinition($myValidatorServiceId, $paramsValidator);
        $this->container->setAlias(self::EXPECTED_PARAMS_VALIDATOR_ALIAS, $myValidatorServiceId);

        $this->loadContainer();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::EXPECTED_REQUEST_HANDLER_SERVICE_ID,
            'setMethodParamsValidator',
            [new Reference(self::EXPECTED_PARAMS_VALIDATOR_ALIAS)]
        );

        $this->assertEndpointIsUsable();
    }

    public function testShouldNotInjectParamsValidatorAliasIfNotDefined()
    {
        $this->loadContainer();

        $handlerDefinition = $this->container->getDefinition(self::EXPECTED_REQUEST_HANDLER_SERVICE_ID);
        foreach ($handlerDefinition->getMethodCalls() as $methodCall) {
            if ('setMethodParamsValidator' === $methodCall[0]) {
                $this->fail('Method call found for method "setMethodParamsValidator"');
            }
        }

        $this->assertEndpointIsUsable();
    }

    public function testShouldBindJsonRpcMethodsToMethodAwareServices()
    {
        $methodAwareServiceServiceId = uniqid();
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

        $methodAwareDefinition = new Definition(JsonRpcMethodAwareInterface::class);
        $methodAwareDefinition->addTag(JsonRpcHttpServerExtension::JSONRPC_METHOD_AWARE_TAG);
        $this->setDefinition($methodAwareServiceServiceId, $methodAwareDefinition);

        $this->loadContainer();

        // Assert that method mapping have been correctly injected
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $methodAwareServiceServiceId,
            'addJsonRpcMethod',
            [
                $methodName,
                $jsonRpcMethodServiceId
            ],
            0
        );
        // Assert that method mapping have been correctly injected
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $methodAwareServiceServiceId,
            'addJsonRpcMethod',
            [
                $methodName2,
                $jsonRpcMethodServiceId2
            ],
            1
        );

        $this->assertEndpointIsUsable();
    }

    public function testShouldThowAnExceptionIfMethodAwareServiceDoesNotImplementRightInterface()
    {
        $methodAwareServiceServiceId = uniqid();

        $methodAwareDefinition = new Definition(\stdClass::class);
        $methodAwareDefinition->addTag(JsonRpcHttpServerExtension::JSONRPC_METHOD_AWARE_TAG);
        $this->setDefinition($methodAwareServiceServiceId, $methodAwareDefinition);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'Service "%s" is tagged as JSON-RPC method aware but does not implement %s',
            $methodAwareServiceServiceId,
            JsonRpcMethodAwareInterface::class
        ));

        $this->loadContainer();
    }

    public function testShouldAddDebugResponseErrorNormalizerIfDebugModeEnabled()
    {
        $this->loadContainer([
            'debug' => [
                'enabled' => true,
            ]
        ]);

        // Assert response normalizer has responseErrorNormalizer as first argument
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'json_rpc_server_sdk.app.serialization.jsonrpc_response_normalizer',
            0,
            new Reference('json_rpc_server_sdk.app.serialization.jsonrpc_response_error_normalizer'),
        );

        $this->assertEndpointIsUsable();
    }
}
