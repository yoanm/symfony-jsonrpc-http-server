<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;

/**
 * Class JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtension implements ExtensionInterface, CompilerPassInterface
{
    // Extension identifier (used in configuration for instance)
    const EXTENSION_IDENTIFIER = 'json_rpc_http_server';

    /** Tags */
    // Server dispatcher - Use this tag and server dispatcher will be injected
    const JSONRPC_SERVER_DISPATCHER_AWARE_TAG = 'json_rpc_http_server.server_dispatcher_aware';


    /** Method resolver */
    const METHOD_RESOLVER_ALIAS = 'json_rpc_http_server.alias.method_resolver';
    /** Params validator */
    const PARAMS_VALIDATOR_ALIAS = 'json_rpc_http_server.alias.params_validator';

    const REQUEST_HANDLER_SERVICE_ID = 'json_rpc_server_sdk.app.handler.jsonrpc_request';

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
        $loader->load('services.public.yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->bindJsonRpcServerDispatcher($container);
        $this->bindValidatorIfDefined($container);
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
     * @param ContainerBuilder $container
     *
     * @return Reference|null Null in case no dispatcher found
     */
    private function bindJsonRpcServerDispatcher(ContainerBuilder $container)
    {
        $dispatcherRef = new Reference('json_rpc_http_server.dispatcher.server');
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

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function compileAndProcessConfigurations(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = (new Processor())->processConfiguration($configuration, $configs);

        $httpEndpointPath = $config['endpoint'];

        $container->setParameter(self::EXTENSION_IDENTIFIER.'.http_endpoint_path', $httpEndpointPath);
    }

    private function bindValidatorIfDefined(ContainerBuilder $container)
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
}
