# JSON-RPC server symfony plugin
 [![License](https://img.shields.io/github/license/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server) [![Code size](https://img.shields.io/github/languages/code-size/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server)

[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/yoanm/symfony-jsonrpc-http-server.svg?label=Scrutinizer&logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/build-status/master) [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/yoanm/symfony-jsonrpc-http-server/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/yoanm/symfony-jsonrpc-http-server/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master)

[![Travis Build Status](https://img.shields.io/travis/yoanm/symfony-jsonrpc-http-server/master.svg?label=Travis&logo=travis)](https://travis-ci.org/yoanm/symfony-jsonrpc-http-server) [![Travis PHP versions](https://img.shields.io/travis/php-v/yoanm/symfony-jsonrpc-http-server.svg?logo=travis)](https://travis-ci.org/yoanm/symfony-jsonrpc-http-server) [![Travis Symfony Versions](https://img.shields.io/badge/Symfony-v3%20%2F%20v4-8892BF.svg?logo=travis)](https://php.net/)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server)

Symfony JSON-RPC HTTP Server to convert an HTTP json-rpc request into HTTP json-rpc response

## How to use

You can either use this library as a simple extension or like any symfony bundle.

*[Behat demo app configuration folders](./features/demo_app/) can be used as examples.*

### With Symfony bundle

 - Add the bundles in your `config/bundles.php` file:
   ```php
   // config/bundles.php
   return [
       ...
       Yoanm\SymfonyJsonRpcHttpServer\JsonRpcHttpServerBundle::class => ['all' => true],
       ...
   ];
   ```
   
 - Add the following in your routing configuration :
   ```yaml
   # config/routes.yaml
   json-rpc-endpoint:
     resource: '@JsonRpcHttpServerBundle/Resources/config/routing/endpoint.xml'
   ```
   
 - Add the following in your configuration :
   ```yaml
   # config/config.yaml
   json_rpc_http_server: ~
   ```

### With Symfony extension only
 - Load the extension in your kernel :
   ```php
   // src/Kernel.php
   ...
   use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
   use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
   ...
   class Kernel
   {
       use MicroKernelTrait;
       ....
       /**
       * {@inheritdoc}
       */
      protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
      {
          /**** Add and load extension **/
          $container->registerExtension($extension = new JsonRpcHttpServerExtension());
          // If you use Symfony Config component, add "json_rpc_http_server: ~" in your configuration.
          // Else load it there
          $container->loadFromExtension($extension->getAlias());
          
          ...
      }
       ....
   }
   ```
   
 - Map your your JSON-RPC methods, see **JSON-RPC Method mapping** section below
 - Manually configure an endpoint, see **Routing** section below

## JSON-RPC Method mapping
You have many ways to inject you json-rpc methods :
 - If you use the bundle, you can do it by configuration :
   ```yaml
   # config/config.yaml
   json_rpc_http_server:
       methods_mapping:
           method-a: '@method-a.service-id'
           method-b: 
               service: '@method-b.service-id'
               aliases: 'method-b-alias'
           method-c: 
               service: '@method-c.service-id'
               aliases: ['method-c-alias-1', 'method-c-alias-2']
   ```
 - You can use tag in the service definition as below :
   ```yaml
   services:
     method-a.service-id:
       class: Method\A\Class
       public: true # <= do no forget the set visibility to public !
       tags:
         - { name: 'json_rpc_http_server.jsonrpc_method', method: 'method-a' }
         - { name: 'json_rpc_http_server.jsonrpc_method', method: 'method-a-alias' }
   ```
 - Inject manually your mapping during container building
   ```php
   // src/Kernel.php
   ...
   use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
   use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;
   ...
   class Kernel implements CompilerPassInterface
   {
       ....
       /**
        * {@inheritdoc}
        */
       public function process(ContainerBuilder $container)
       {
           $container->getDefinition(JsonRpcHttpServerExtension::SERVICE_NAME_RESOLVER_SERVICE_NAME)
               ->addMethodCall('addMethodMapping', ['method-a', 'method-a.service-id'])
               ->addMethodCall('addMethodMapping', ['method-b', 'method-b.service-id'])
               ->addMethodCall('addMethodMapping', ['method-b-alias', 'method-b.service-id'])
           ;
       }
       ....
   }
   ```
 - Or inject manually your mapping after container building
   ```php
   $container->get(JsonRpcHttpServerExtension::SERVICE_NAME_RESOLVER_SERVICE_NAME)
       ->addMethodMapping('method-a', 'method-a.service-id')
       ->addMethodMapping('method-b', 'method-b.service-id')
       ->addMethodMapping('method-b-alias', 'method-b.service-id')
   ;
   ```
 
## Routing
 - If you use the bundle, the default endpoint is `/json-rcp`. You can custome it by using : 
   ```yaml
   # config/config.yaml
   json_rpc_http_server: 
       endpoint: '/my-custom-endpoint'
   ```
   
 - Or you can define your own route and bind the endpoint as below :
   ```yaml
   # config/routes.yaml
   index:
       path: /my-json-rpc-endpoint
       defaults: { _controller: 'json_rpc_http_server.endpoint:index' }
   ```
   
## Custom method resolver

By default this bundle use [`yoanm/jsonrpc-server-sdk-psr11-resolver`](https://github.com/yoanm/php-jsonrpc-server-sdk-psr11-resolver).

In case you want to use your own, you can do it by using : 

### Service definition tag
Use `json_rpc_http_server.method_resolver` tag as following:
```yaml
services:
  my.custom_method_resolver.service:
    class: Custom\Method\Resolver\Class
    tags: ['json_rpc_http_server.method_resolver']
```

### Bundle configuration
Configure the bundle as below
```yaml
# config/config.yaml
json_rpc_http_server:
    method_resolver: '@my.custom_method_resolver.service'
```
   

## Contributing
See [contributing note](./CONTRIBUTING.md)
