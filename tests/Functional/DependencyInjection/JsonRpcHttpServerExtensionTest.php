<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Tests\Common\DependencyInjection\ConcreteJsonRpcServerDispatcherAware;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Resolver\ServiceNameResolver;

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
}
