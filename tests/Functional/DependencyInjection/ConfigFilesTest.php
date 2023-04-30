<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseErrorNormalizer;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
use Yoanm\SymfonyJsonRpcHttpServer\Dispatcher\SymfonyJsonRpcServerDispatcher;
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Resolver\MethodResolver;

/**
 * /!\ This test class does not cover JsonRpcHttpServerExtension, it covers yaml configuration files
 * => So no [at]covers tag !
 * @coversNothing
 */
class ConfigFilesTest extends AbstractTestClass
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

    /**
     * @dataProvider provideSDKAppServiceIdAndClass
     * @dataProvider provideSDKInfraServiceIdAndClass
     * @dataProvider provideBundlePublicServiceIdAndClass
     *
     * @param string $serviceId
     * @param string $expectedClassName
     * @param bool   $public
     */
    public function testShouldHaveService($serviceId, $expectedClassName, $public)
    {
        $this->loadContainer([], true, false);

        $this->assertContainerBuilderHasService($serviceId, $expectedClassName);
        if (true === $public) {
            // Check that service is accessible through the container
            $this->assertNotNull($this->container->get($serviceId));
        }
    }

    /**
     * @return array
     */
    public function provideSDKAppServiceIdAndClass()
    {
        return [
            'SDK - App - Request Denormalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_request_denormalizer',
                'serviceClassName' => JsonRpcRequestDenormalizer::class,
                'public' => false,
            ],
            'SDK - App - Response Normalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_response_normalizer',
                'serviceClassName' => JsonRpcResponseNormalizer::class,
                'public' => false,
            ],
            'SDK - App - Call Denormalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_call_dernormalizer',
                'serviceClassName' => JsonRpcCallDenormalizer::class,
                'public' => false,
            ],
            'SDK - App - Call response Normalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_call_response_normalizer',
                'serviceClassName' => JsonRpcCallResponseNormalizer::class,
                'public' => false,
            ],
            'SDK - App - Call Serializer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_call_serializer',
                'serviceClassName' => JsonRpcCallSerializer::class,
                'public' => false,
            ],
            'SDK - App - Request Handler' => [
                'serviceId' => 'json_rpc_server_sdk.app.handler.jsonrpc_request',
                'serviceClassName' => JsonRpcRequestHandler::class,
                'public' => false,
            ],
            'SDK - App - Exception Handler' => [
                'serviceId' => 'json_rpc_server_sdk.app.handler.exception',
                'serviceClassName' => ExceptionHandler::class,
                'public' => false,
            ],
            'SDK - App - Response Creator' => [
                'serviceId' => 'json_rpc_server_sdk.app.creator.response',
                'serviceClassName' => ResponseCreator::class,
                'public' => false,
            ],
            'SDK - App - Response error normalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_response_error_normalizer',
                'serviceClassName' => JsonRpcResponseErrorNormalizer::class,
                'public' => false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideSDKInfraServiceIdAndClass()
    {
        return [
            'SDK - Infra - Endpoint' => [
                'serviceId' => 'json_rpc_server_sdk.infra.endpoint',
                'serviceClassName' => JsonRpcEndpoint::class,
                'public' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideBundlePublicServiceIdAndClass()
    {
        return [
            'Bundle - Public - HTTP endpoint' => [
                'serviceId' => 'json_rpc_http_server.endpoint',
                'serviceClassName' => JsonRpcHttpEndpoint::class,
                'public' => true,
            ],
            'Bundle - Public - Event Dispatcher' => [
                'serviceId' => 'json_rpc_http_server.dispatcher.server',
                'serviceClassName' => SymfonyJsonRpcServerDispatcher::class,
                'public' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideBundlePrivateServiceIdAndClass()
    {
        return [
            'Bundle - Private - JSON-RPC method resolver ServiceLocator' => [
                'serviceId' => 'json_rpc_http_server.service_locator.method_resolver',
                'serviceClassName' => ServiceLocator::class,
                'public' => true,
            ],
            'Bundle - Private - MethodResolver alias' => [
                'serviceId' => 'json_rpc_http_server.alias.method_resolver',
                'serviceClassName' => MethodResolver::class,
                'public' => true,
            ],
        ];
    }
}
