<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Tests\Common\DependencyInjection\ConcreteJsonRpcServerDispatcherAware;
use Tests\Common\Mock\ConcreteParamsValidator;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionTest extends AbstractTestClass
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new JsonRpcHttpServerExtension()
        ];
    }


    public function testShouldBeLoadable()
    {
        $this->load();

        $this->assertEndpointIsUsable();
    }

    public function testShouldManageCustomEndpointPathFromConfiguration()
    {
        $myCustomEndpoint = 'my-custom-endpoint';
        $this->load(['endpoint' => $myCustomEndpoint]);

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

        $this->load();

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

        $this->load();
    }

    public function testShouldInjectParamsValidatorAliasIfDefined()
    {
        $myValidatorServiceId = 'my-params-validator-service';
        $paramsValidator = new Definition(ConcreteParamsValidator::class);

        $this->setDefinition($myValidatorServiceId, $paramsValidator);
        $this->container->setAlias(JsonRpcHttpServerExtension::PARAMS_VALIDATOR_ALIAS, $myValidatorServiceId);

        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            JsonRpcHttpServerExtension::REQUEST_HANDLER_SERVICE_ID,
            'setMethodParamsValidator',
            [new Reference(JsonRpcHttpServerExtension::PARAMS_VALIDATOR_ALIAS)]
        );

        $this->assertEndpointIsUsable();
    }


    /**
     * @group yo
     */
    public function testShouldNotInjectParamsValidatorAliasIfNotDefined()
    {
        $this->load();

        $handlerDefinition = $this->container->getDefinition(JsonRpcHttpServerExtension::REQUEST_HANDLER_SERVICE_ID);
        foreach ($handlerDefinition->getMethodCalls() as $methodCall) {
            if ('setMethodParamsValidator' === $methodCall[0]) {
                $this->fail('Method call found for method "setMethodParamsValidator"');
            }
        }

        $this->assertEndpointIsUsable();
    }
}
