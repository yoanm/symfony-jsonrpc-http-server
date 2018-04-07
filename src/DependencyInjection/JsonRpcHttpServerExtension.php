<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
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
use Yoanm\SymfonyJsonRpcHttpServer\Endpoint\JsonRpcHttpEndpoint;
use Yoanm\SymfonyJsonRpcHttpServer\Resolver\ServiceNameResolver; // <= Must stay optional !

/**
 * Class JsonRpcHttpServerExtension
 *
 * /!\ In case you use the default resolver (yoanm/jsonrpc-server-sdk-psr11-resolver),
 * your JSON-RPC method services must be public in order to retrieve it later from container
 */
class JsonRpcHttpServerExtension implements ExtensionInterface, CompilerPassInterface
{
    // Use this service to inject string request
    const ENDPOINT_SERVICE_NAME = 'json_rpc_http_server.endpoint';

    // Use this tag to inject your own resolver
    const METHOD_RESOLVER_TAG = 'json_rpc_http_server.method_resolver';

    // Use this tag to inject your JSON-RPC methods into the default method resolver
    const JSONRPC_METHOD_TAG = 'json_rpc_http_server.jsonrpc_method';

    // In case you want to add mapping for a method, use the following service
    const SERVICE_NAME_RESOLVER_SERVICE_NAME = 'json_rpc_http_server.resolver.service_name';
    // And add an attribute with following key
    const JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';

    // Extension identifier (used in configuration for instance)
    const EXTENSION_IDENTIFIER = 'json_rpc_http_server';

    const HTTP_ENDPOINT_PATH = '/json-rpc';


    /** Private constants */
    const CUSTOM_METHOD_RESOLVER_CONTAINER_PARAM = self::EXTENSION_IDENTIFIER.'.custom_method_resolver';
    const METHODS_MAPPING_CONTAINER_PARAM = self::EXTENSION_IDENTIFIER.'.methods_mapping';
    const HTTP_ENDPOINT_PATH_CONTAINER_PARAM = self::EXTENSION_IDENTIFIER.'.http_endpoint_path';

    /** @var bool */
    private $parseConfig = false;

    /**
     * @param bool|false $parseConfig If true, Config component is required
     */
    public function __construct(bool $parseConfig = false)
    {
        $this->parseConfig = $parseConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->compileAndProcessConfigurations($configs, $container);

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
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::EXTENSION_IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $isContainerResolver = $this->aliasMethodResolver($container);
        if (true === $isContainerResolver) {
            $this->loadJsonRpcMethods($container);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createAppServiceDefinitions(ContainerBuilder $container)
    {
        // RequestDenormalizer
        $container->setDefinition(
            'json_rpc_http_server.sdk.app.serialization.request_denormalizer',
            new Definition(RequestDenormalizer::class)
        );
        // ResponseNormalizer
        $container->setDefinition(
            'json_rpc_http_server.sdk.app.serialization.response_normalizer',
            new Definition(ResponseNormalizer::class)
        );
        // ResponseCreator
        $container->setDefinition(
            'json_rpc_http_server.sdk.app.creator.response',
            new Definition(ResponseCreator::class)
        );
        // CustomExceptionCreator
        $container->setDefinition(
            'json_rpc_http_server.sdk.app.creator.custom_exception',
            new Definition(CustomExceptionCreator::class)
        );

        // MethodManager
        $container->setDefinition(
            'json_rpc_http_server.sdk.app.manager.method',
            new Definition(
                MethodManager::class,
                [
                    new Reference('json_rpc_http_server.infra.resolver.method'),
                    new Reference('json_rpc_http_server.sdk.app.creator.custom_exception')
                ]
            )
        );
        // RequestHandler
        $container->setDefinition(
            'json_rpc_http_server.sdk.app.handler.request',
            new Definition(
                RequestHandler::class,
                [
                    new Reference('json_rpc_http_server.sdk.app.manager.method'),
                    new Reference('json_rpc_http_server.sdk.app.creator.response')
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
            'json_rpc_http_server.sdk.infra.serialization.raw_request_serializer',
            new Definition(
                RawRequestSerializer::class,
                [new Reference('json_rpc_http_server.sdk.app.serialization.request_denormalizer')]
            )
        );

        // RawResponseSerializer
        $container->setDefinition(
            'json_rpc_http_server.sdk.infra.serialization.raw_response_serializer',
            new Definition(
                RawResponseSerializer::class,
                [new Reference('json_rpc_http_server.sdk.app.serialization.response_normalizer')]
            )
        );
        // JsonRpcEndpoint
        $container->setDefinition(
            'json_rpc_http_server.sdk.infra.endpoint',
            new Definition(
                JsonRpcEndpoint::class,
                [
                    new Reference('json_rpc_http_server.sdk.infra.serialization.raw_request_serializer'),
                    new Reference('json_rpc_http_server.sdk.app.handler.request'),
                    new Reference('json_rpc_http_server.sdk.infra.serialization.raw_response_serializer'),
                    new Reference('json_rpc_http_server.sdk.app.creator.response')
                ]
            )
        );
        // ContainerMethodResolver
        $container->setDefinition(
            'json_rpc_http_server.psr11.infra.resolver.method',
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
                    new Reference('json_rpc_http_server.sdk.infra.endpoint')
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
     * @return bool Whether it is a ContainerResolver or not
     */
    private function aliasMethodResolver(ContainerBuilder $container)
    {
        $isContainerResolver = false;
        if ($container->hasParameter(self::CUSTOM_METHOD_RESOLVER_CONTAINER_PARAM)) {
            $resolverServiceId = $container->getParameter(self::CUSTOM_METHOD_RESOLVER_CONTAINER_PARAM);
        } else {
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
                $resolverServiceId = 'json_rpc_http_server.psr11.infra.resolver.method';
                $isContainerResolver = true;
            }
        }

        $container->setAlias('json_rpc_http_server.infra.resolver.method', $resolverServiceId);

        return $isContainerResolver;
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadJsonRpcMethods(ContainerBuilder $container)
    {
        // Check if methods have been defined by tags
        $methodServiceList = $container->findTaggedServiceIds(self::JSONRPC_METHOD_TAG);

        foreach ($methodServiceList as $externalServiceIdString => $tagAttributeList) {
            $serviceId = $this->cleanExternalServiceIdString($externalServiceIdString);
            $this->checkJsonRpcMethodService($container, $serviceId);
            $methodNameList = $this->extractMethodNameList($tagAttributeList, $serviceId);
            foreach ($methodNameList as $methodName) {
                $this->injectMethodMappingToServiceNameResolver($methodName, $serviceId, $container);
            }
        }

        if ($container->hasParameter(self::METHODS_MAPPING_CONTAINER_PARAM)) {
            foreach ($container->getParameter(self::METHODS_MAPPING_CONTAINER_PARAM) as $methodName => $mappingConfig) {
                $serviceId = $this->cleanExternalServiceIdString($mappingConfig['service']);
                $this->checkJsonRpcMethodService($container, $serviceId);
                $this->injectMethodMappingToServiceNameResolver($methodName, $serviceId, $container);
                foreach ($mappingConfig['aliases'] as $methodAlias) {
                    $this->injectMethodMappingToServiceNameResolver($methodAlias, $serviceId, $container);
                }
            }
        }
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

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function compileAndProcessConfigurations(array $configs, ContainerBuilder $container)
    {
        $httpEndpointPath = self::HTTP_ENDPOINT_PATH;
        if (true === $this->parseConfig) {
            $configuration = new Configuration();
            $config = (new Processor())->processConfiguration($configuration, $configs);

            if (array_key_exists('method_resolver', $config) && $config['method_resolver']) {
                $container->setParameter(
                    self::CUSTOM_METHOD_RESOLVER_CONTAINER_PARAM,
                    $this->cleanExternalServiceIdString($config['method_resolver'])
                );
            }
            if (array_key_exists('methods_mapping', $config) && is_array($config['methods_mapping'])) {
                $container->setParameter(self::METHODS_MAPPING_CONTAINER_PARAM, $config['methods_mapping']);
            }
            if (array_key_exists('http_endpoint_path', $config)) {
                $httpEndpointPath = $config['http_endpoint_path'];
            }
        }

        $container->setParameter(self::HTTP_ENDPOINT_PATH_CONTAINER_PARAM, $httpEndpointPath);
    }

    /**
     * @param string           $methodName
     * @param string           $serviceId
     * @param ContainerBuilder $container
     */
    private function injectMethodMappingToServiceNameResolver(
        string $methodName,
        string $serviceId,
        ContainerBuilder $container
    ) {
        $container->getDefinition(self::SERVICE_NAME_RESOLVER_SERVICE_NAME)
            ->addMethodCall('addMethodMapping', [$methodName, $serviceId]);
    }

    private function cleanExternalServiceIdString(string $externalServiceIdString)
    {
        if ('@' === $externalServiceIdString[0]) {
            return substr($externalServiceIdString, 1);
        }

        return $externalServiceIdString;
    }
}
