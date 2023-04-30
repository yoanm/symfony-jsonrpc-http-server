<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodAwareInterface;

/**
 * @see \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\Configuration
 */
class JsonRpcHttpServerExtension implements ExtensionInterface, CompilerPassInterface
{
    // Extension identifier (used in configuration for instance)
    public const EXTENSION_IDENTIFIER = 'json_rpc_http_server';

    public const ENDPOINT_PATH_CONTAINER_PARAM_ID = self::EXTENSION_IDENTIFIER.'.http_endpoint_path';

    /** Tags */
    /**** Methods tags **/
    // Use this tag to inject your JSON-RPC methods into the default method resolver
    public const JSONRPC_METHOD_TAG = 'json_rpc_http_server.jsonrpc_method';
    // And add an attribute with following key
    public const JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';
    /**** END - Methods tags **/

    // Server dispatcher - Use this tag and server dispatcher will be injected
    public const JSONRPC_SERVER_DISPATCHER_AWARE_TAG = 'json_rpc_http_server.server_dispatcher_aware';

    // JSON-RPC Methods mapping - Use this tag and all JSON-RPC method instance will be injected
    // Useful for documentation for instance
    public const JSONRPC_METHOD_AWARE_TAG = 'json_rpc_http_server.method_aware';


    private const PARAMS_VALIDATOR_ALIAS = 'json_rpc_http_server.alias.params_validator';
    private const REQUEST_HANDLER_SERVICE_ID = 'json_rpc_server_sdk.app.handler.jsonrpc_request';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->compileAndProcessConfigurations($configs, $container);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('sdk.services.app.yaml');
        $loader->load('sdk.services.infra.yaml');
        $loader->load('services.private.yaml');
        $loader->load('services.public.yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->bindJsonRpcServerDispatcher($container);
        $this->bindValidatorIfDefined($container);
        $this->bindJsonRpcMethods($container);
        $this->bindDebug($container);
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
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function compileAndProcessConfigurations(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = (new Processor())->processConfiguration($configuration, $configs);

        $container->setParameter(self::ENDPOINT_PATH_CONTAINER_PARAM_ID, $config['endpoint']);

        foreach ($config['debug'] as $name => $value) {
            $container->setParameter(self::EXTENSION_IDENTIFIER.'.debug.'.$name, $value);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function bindJsonRpcServerDispatcher(ContainerBuilder $container) : void
    {
        $dispatcherRef = new Reference(self::EXTENSION_IDENTIFIER.'.dispatcher.server');
        $dispatcherAwareServiceList = $container->findTaggedServiceIds(self::JSONRPC_SERVER_DISPATCHER_AWARE_TAG);
        foreach ($dispatcherAwareServiceList as $serviceId => $tagAttributeList) {
            $definition = $container->getDefinition($serviceId);

            if (!in_array(JsonRpcServerDispatcherAwareTrait::class, class_uses($definition->getClass()))) {
                throw new LogicException(
                    sprintf(
                        'Service "%s" is taggued with "%s" but does not use "%s"',
                        $serviceId,
                        self::JSONRPC_SERVER_DISPATCHER_AWARE_TAG,
                        JsonRpcServerDispatcherAwareTrait::class
                    )
                );
            }

            $definition->addMethodCall('setJsonRpcServerDispatcher', [$dispatcherRef]);
        }
    }

    private function bindValidatorIfDefined(ContainerBuilder $container) : void
    {
        if ($container->hasAlias(self::PARAMS_VALIDATOR_ALIAS)) {
            $container->getDefinition(self::REQUEST_HANDLER_SERVICE_ID)
                ->addMethodCall(
                    'setMethodParamsValidator',
                    [
                        new Reference(self::PARAMS_VALIDATOR_ALIAS)
                    ]
                )
            ;
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function bindJsonRpcMethods(ContainerBuilder $container) : void
    {
        $mappingAwareServiceDefinitionList = $this->findAndValidateMappingAwareDefinitionList($container);

        $jsonRpcMethodDefinitionList = (new JsonRpcMethodDefinitionHelper())
            ->findAndValidateJsonRpcMethodDefinition($container);

        $methodMappingList = [];
        foreach ($jsonRpcMethodDefinitionList as $jsonRpcMethodServiceId => $methodNameList) {
            foreach ($methodNameList as $methodName) {
                $methodMappingList[$methodName] = new Reference($jsonRpcMethodServiceId);
                $this->bindJsonRpcMethod($methodName, $jsonRpcMethodServiceId, $mappingAwareServiceDefinitionList);
            }
        }

        // Service locator for method resolver
        // => first argument is an array of wanted service with keys as alias for internal use
        $container->getDefinition(self::EXTENSION_IDENTIFIER.'.service_locator.method_resolver')
            ->setArgument(0, $methodMappingList);
    }

    /**
     * @param string       $methodName
     * @param string       $jsonRpcMethodServiceId
     * @param Definition[] $mappingAwareServiceDefinitionList
     */
    private function bindJsonRpcMethod(
        string $methodName,
        string $jsonRpcMethodServiceId,
        array $mappingAwareServiceDefinitionList
    ) : void {
        foreach ($mappingAwareServiceDefinitionList as $methodAwareServiceDefinition) {
            $methodAwareServiceDefinition->addMethodCall(
                'addJsonRpcMethod',
                [$methodName, new Reference($jsonRpcMethodServiceId)]
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function findAndValidateMappingAwareDefinitionList(ContainerBuilder $container): array
    {
        $mappingAwareServiceDefinitionList = [];
        $methodAwareServiceIdList = array_keys($container->findTaggedServiceIds(self::JSONRPC_METHOD_AWARE_TAG));
        foreach ($methodAwareServiceIdList as $serviceId) {
            $definition = $container->getDefinition($serviceId);

            $this->checkMethodAwareServiceIdList($definition, $serviceId, $container);

            $mappingAwareServiceDefinitionList[$serviceId] = $definition;
        }

        return $mappingAwareServiceDefinitionList;
    }

    private function checkMethodAwareServiceIdList(
        Definition $definition,
        string $serviceId,
        ContainerBuilder $container
    ) : void {
        $class = $container->getReflectionClass($definition->getClass());

        if (null !== $class && !$class->implementsInterface(JsonRpcMethodAwareInterface::class)) {
            throw new LogicException(sprintf(
                'Service "%s" is tagged as JSON-RPC method aware but does not implement %s',
                $serviceId,
                JsonRpcMethodAwareInterface::class
            ));
        }
    }

    private function bindDebug(ContainerBuilder $container) : void
    {
        if ($container->getParameter(self::EXTENSION_IDENTIFIER.'.debug.enabled')) {
            $container->getDefinition('json_rpc_server_sdk.app.serialization.jsonrpc_response_normalizer')
                ->addArgument(new Reference('json_rpc_server_sdk.app.serialization.jsonrpc_response_error_normalizer'));
        }
    }
}
