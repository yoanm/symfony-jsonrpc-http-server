<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Tests\Common\Mock\ConcreteResolver;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
use Yoanm\SymfonyJsonRpcHttpServer\Dispatcher\SymfonyJsonRpcServerDispatcher;
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Listener\ServerDocCreatedListener;

/**
 * /!\ This test class does not cover JsonRpcHttpServerExtension, it covers yaml configuration files
 * => So no [at]covers tag !
 */
class ConfigFilesTest extends AbstractTestClass
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

    /**
     * @dataProvider provideSDKAppServiceIdAndClass
     * @dataProvider provideSDKInfraServiceIdAndClass
     * @dataProvider provideBundlePublicServiceIdAndClass
     *
     * @param string $serviceId
     * @param string $expectedClassName
     */
    public function testShouldExposeUsableService($serviceId, $expectedClassName)
    {
        $this->load([], true, false);

        $this->assertContainerBuilderHasService($serviceId, $expectedClassName);
        // Check that service is accessible through the container
        $this->assertNotNull($this->container->get($serviceId));
    }

    /**
     * @return array
     */
    public function provideSDKAppServiceIdAndClass()
    {
        return [
            'SDK - App - Request Denormalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_request_denormalizer',
                'serviceClassName' => JsonRpcRequestDenormalizer::class
            ],
            'SDK - App - Response Normalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_response_normalizer',
                'serviceClassName' => JsonRpcResponseNormalizer::class
            ],
            'SDK - App - Call Denormalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_call_dernormalizer',
                'serviceClassName' => JsonRpcCallDenormalizer::class
            ],
            'SDK - App - Call response Normalizer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_call_response_normalizer',
                'serviceClassName' => JsonRpcCallResponseNormalizer::class
            ],
            'SDK - App - Call Serializer' => [
                'serviceId' => 'json_rpc_server_sdk.app.serialization.jsonrpc_call_serializer',
                'serviceClassName' => JsonRpcCallSerializer::class
            ],
            'SDK - App - Request Handler' => [
                'serviceId' => 'json_rpc_server_sdk.app.handler.jsonrpc_request',
                'serviceClassName' => JsonRpcRequestHandler::class
            ],
            'SDK - App - Exception Handler' => [
                'serviceId' => 'json_rpc_server_sdk.app.handler.exception',
                'serviceClassName' => ExceptionHandler::class
            ],
            'SDK - App - Response Creator' => [
                'serviceId' => 'json_rpc_server_sdk.app.creator.response',
                'serviceClassName' => ResponseCreator::class
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
                'serviceClassName' => JsonRpcEndpoint::class
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
                'serviceClassName' => JsonRpcHttpEndpoint::class
            ],
            'Bundle - Public - Event Dispatcher' => [
                'serviceId' => 'json_rpc_http_server.dispatcher.server',
                'serviceClassName' => SymfonyJsonRpcServerDispatcher::class
            ],
        ];
    }
}
