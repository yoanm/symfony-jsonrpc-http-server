<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Endpoint\JsonRpcHttpEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Resolver\ServiceNameResolver;

/**
 * Class JsonRpcHttpServerExtension
 *
 * /!\ In case you use the default resolver (yoanm/jsonrpc-server-sdk-psr11-resolver),
 * your JSON-RPC method services must be public in order to retrieve it later from container
 */
class JsonRpcHttpServerExtension implements ExtensionInterface, CompilerPassInterface
{
    // Use this service to inject string request
    const ENDPOINT_SERVICE_NAME = 'yoanm.jsonrpc_http_server.endpoint';
    // Use this tag to inject your own resolver
    const METHOD_RESOLVER_TAG = 'yoanm.jsonrpc_http_server.method_resolver';
    // Use this tag to inject your JSON-RPC methods into the default method resolver
    const JSONRPC_METHOD_TAG = 'yoanm.jsonrpc_http_server.jsonrpc_method';
    // In case you want to add mapping for a method, use the following service
    const SERVICE_NAME_RESOLVER_SERVICE_NAME = 'yoanm.jsonrpc_http_server.resolver.service_name';


    const JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';


    private $sdkAppResponseCreatorServiceId        = 'sdk.app.creator.response';
    private $sdkAppCustomExceptionCreatorServiceId = 'sdk.app.creator.custom_exception';
    private $sdkAppRequestDenormalizerServiceId    = 'sdk.app.serialization.request_denormalizer';
    private $sdkAppResponseNormalizerServiceId     = 'sdk.app.serialization.response_normalizer';
    private $sdkAppMethodManagerServiceId          = 'sdk.app.manager.method';
    private $sdkAppRequestHandlerServiceId         = 'sdk.app.handler.request';

    private $sdkInfraEndpointServiceId          = 'sdk.infra.endpoint';
    private $sdkInfraRawReqSerializerServiceId  = 'sdk.infra.serialization.raw_request_serializer';
    private $sdkInfraRawRespSerializerServiceId = 'sdk.infra.serialization.raw_response_serializer';

    private $psr11InfraMethodResolverServiceId = 'psr11.infra.resolver.method';

    private $methodResolverStubServiceId = 'infra.resolver.method';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Use only references to avoid class instantiation
        // And don't use file configuration in order to not add Symfony\Component\Config as dependency
        $this->createPublicServiceDefinitions($container);
        $this->createInfraServiceDefinitions($container);
        $this->createAppServiceDefinitions($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://example.org/schema/dic/'.$this->getAlias();
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'yoanm_jsonrpc_http_server';
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $isContainerResolver = $this->aliasMethodResolver($container);
        if (true === $isContainerResolver) {
            $this->loadJsonRpcMethodsFromTag($container);
        }
    }


    /**
     * @param ContainerBuilder $container
     */
    protected function createAppServiceDefinitions(ContainerBuilder $container)
    {
        // RequestDenormalizer
        $container->setDefinition(
            $this->prependServiceName($this->sdkAppRequestDenormalizerServiceId),
            new Definition(RequestDenormalizer::class)
        );
        // ResponseNormalizer
        $container->setDefinition(
            $this->prependServiceName($this->sdkAppResponseNormalizerServiceId),
            new Definition(ResponseNormalizer::class)
        );
        // ResponseCreator
        $container->setDefinition(
            $this->prependServiceName($this->sdkAppResponseCreatorServiceId),
            new Definition(ResponseCreator::class)
        );
        // CustomExceptionCreator
        $container->setDefinition(
            $this->prependServiceName($this->sdkAppCustomExceptionCreatorServiceId),
            new Definition(CustomExceptionCreator::class)
        );

        // MethodManager
        $container->setDefinition(
            $this->prependServiceName($this->sdkAppMethodManagerServiceId),
            new Definition(
                MethodManager::class,
                [
                    new Reference($this->prependServiceName($this->methodResolverStubServiceId)),
                    new Reference($this->prependServiceName($this->sdkAppCustomExceptionCreatorServiceId))
                ]
            )
        );
        // RequestHandler
        $container->setDefinition(
            $this->prependServiceName($this->sdkAppRequestHandlerServiceId),
            new Definition(
                RequestHandler::class,
                [
                    new Reference($this->prependServiceName($this->sdkAppMethodManagerServiceId)),
                    new Reference($this->prependServiceName($this->sdkAppResponseCreatorServiceId))
                ]
            )
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createInfraServiceDefinitions(ContainerBuilder $container)
    {
        // RawRequestSerializer
        $container->setDefinition(
            $this->prependServiceName($this->sdkInfraRawReqSerializerServiceId),
            new Definition(
                RawRequestSerializer::class,
                [new Reference($this->prependServiceName($this->sdkAppRequestDenormalizerServiceId))]
            )
        );

        // RawResponseSerializer
        $container->setDefinition(
            $this->prependServiceName($this->sdkInfraRawRespSerializerServiceId),
            new Definition(
                RawResponseSerializer::class,
                [new Reference($this->prependServiceName($this->sdkAppResponseNormalizerServiceId))]
            )
        );
        // JsonRpcEndpoint
        $container->setDefinition(
            $this->prependServiceName($this->sdkInfraEndpointServiceId),
            new Definition(
                JsonRpcEndpoint::class,
                [
                    new Reference($this->prependServiceName($this->sdkInfraRawReqSerializerServiceId)),
                    new Reference($this->prependServiceName($this->sdkAppRequestHandlerServiceId)),
                    new Reference($this->prependServiceName($this->sdkInfraRawRespSerializerServiceId)),
                    new Reference($this->prependServiceName($this->sdkAppResponseCreatorServiceId))
                ]
            )
        );
        // ContainerMethodResolver
        $container->setDefinition(
            $this->prependServiceName($this->psr11InfraMethodResolverServiceId),
            (new Definition(
                ContainerMethodResolver::class,
                [
                    new Reference('service_container')
                ]
            ))->addMethodCall(
                'setServiceNameResolver',
                [
                    new Reference(self::SERVICE_NAME_RESOLVER_SERVICE_NAME)
                ]
            )
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createPublicServiceDefinitions(ContainerBuilder $container)
    {
        // JsonRpcHttpEndpoint
        $container->setDefinition(
            self::ENDPOINT_SERVICE_NAME,
            (new Definition(
                JsonRpcHttpEndpoint::class,
                [
                    new Reference($this->prependServiceName($this->sdkInfraEndpointServiceId))
                ]
            ))->setPublic(true)
        );
        // ServiceNameResolver
        $container->setDefinition(
            self::SERVICE_NAME_RESOLVER_SERVICE_NAME,
            (new Definition(ServiceNameResolver::class))->setPublic(true)
        );
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return bool Wether it is a ContainerResolver or not
     */
    private function aliasMethodResolver(ContainerBuilder $container)
    {
        $isContainerResolver = false;
        $serviceIdList = array_keys($container->findTaggedServiceIds(self::METHOD_RESOLVER_TAG));
        $serviceCount = count($serviceIdList);
        if ($serviceCount > 0) {
            if ($serviceCount > 1) {
                throw new LogicException(
                    sprintf(
                        'Only one method resolver could be defined, found following services : %s',
                        implode(', ', $serviceIdList)
                    )
                );
            }
            // Use the first result
            $resolverServiceId = array_shift($serviceIdList);
        } else {
            // Use ArrayMethodResolver as default resolver
            $resolverServiceId = $this->prependServiceName($this->psr11InfraMethodResolverServiceId);
            $isContainerResolver = true;
        }

        $container->setAlias($this->prependServiceName($this->methodResolverStubServiceId), $resolverServiceId);

        return $isContainerResolver;
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadJsonRpcMethodsFromTag(ContainerBuilder $container)
    {
        // Check if methods have been defined by tags
        $methodServiceList = $container->findTaggedServiceIds(self::JSONRPC_METHOD_TAG);
        $defaultResolverDefinition = $container->getDefinition(self::SERVICE_NAME_RESOLVER_SERVICE_NAME);

        foreach ($methodServiceList as $serviceId => $tagAttributeList) {
            $this->checkJsonRpcMethodService($container, $serviceId);
            $methodNameList = $this->extractMethodNameList($tagAttributeList, $serviceId);
            foreach ($methodNameList as $methodName) {
                $defaultResolverDefinition->addMethodCall('addMethodMapping', [$methodName, $serviceId]);
            }
        }
    }

    /**
     * @param string $serviceName
     *
     * @return string
     */
    private function prependServiceName(string $serviceName) : string
    {
        return sprintf('yoanm.jsonrpc_http_server.%s', $serviceName);
    }

    /**
     * @param array  $tagAttributeList
     * @param string $serviceId
     */
    private function extractMethodNameList(array $tagAttributeList, string $serviceId) : array
    {
        $methodNameList = [];
        foreach ($tagAttributeList as $tagAttributeKey => $tagAttributeData) {
            if (!array_key_exists(self::JSONRPC_METHOD_TAG_METHOD_NAME_KEY, $tagAttributeData)) {
                throw new LogicException(sprintf(
                    'Service "%s" is taggued as JSON-RPC method but does not have'
                    . ' method name defined under "%s" tag attribute key',
                    $serviceId,
                    self::JSONRPC_METHOD_TAG_METHOD_NAME_KEY
                ));
            }
            $methodNameList[] = $tagAttributeData[self::JSONRPC_METHOD_TAG_METHOD_NAME_KEY];
        }

        return $methodNameList;
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $serviceId
     */
    private function checkJsonRpcMethodService(ContainerBuilder $container, string $serviceId)
    {
        // Check if given service is public => must be public in order to get it from container later
        if (!$container->getDefinition($serviceId)->isPublic()) {
            throw new LogicException(sprintf(
                'Service "%s" is taggued as JSON-RPC method but is not public. Service must be public in order'
                . ' to retrieve it later',
                $serviceId
            ));
        }
    }
}
