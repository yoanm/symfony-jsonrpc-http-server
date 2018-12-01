# JSON-RPC server symfony plugin
[![License](https://img.shields.io/github/license/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server) [![Code size](https://img.shields.io/github/languages/code-size/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server) [![Dependencies](https://img.shields.io/librariesio/github/yoanm/symfony-jsonrpc-http-server.svg)](https://libraries.io/packagist/yoanm%2Fsymfony-jsonrpc-http-server)

[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/yoanm/symfony-jsonrpc-http-server.svg?label=Scrutinizer&logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/build-status/master) [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/yoanm/symfony-jsonrpc-http-server/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/yoanm/symfony-jsonrpc-http-server/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master)

[![Travis Build Status](https://img.shields.io/travis/yoanm/symfony-jsonrpc-http-server/master.svg?label=Travis&logo=travis)](https://travis-ci.org/yoanm/symfony-jsonrpc-http-server) [![Travis PHP versions](https://img.shields.io/travis/php-v/yoanm/symfony-jsonrpc-http-server.svg?logo=travis)](https://php.net/) [![Travis Symfony Versions](https://img.shields.io/badge/Symfony-v3%20%2F%20v4-8892BF.svg?logo=travis)](https://symfony.com/)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server) [![Packagist PHP version](https://img.shields.io/packagist/php-v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server)

Symfony JSON-RPC HTTP Server to convert an HTTP json-rpc request into HTTP json-rpc response

## How to use

Bundle requires only two things : 
 - A method resolver which is compatible with [`yoanm/jsonrpc-server-sdk`](https://raw.githubusercontent.com/yoanm/php-jsonrpc-server-sdk)
 - JSON-RPC Methods which are compatible with [`yoanm/jsonrpc-server-sdk`](https://raw.githubusercontent.com/yoanm/php-jsonrpc-server-sdk)
 
*[Behat demo app configuration folders](./features/demo_app/) can be used as examples.*

 - Add the bundles in your `config/bundles.php` file:
   ```php
   // config/bundles.php
   return [
       ...
       Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
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
   framework:
     secret: '%env(APP_SECRET)%'

   json_rpc_http_server: ~
   # Or the following in case you want to customize endpoint path
   #json_rpc_http_server:
   #  endpoint: '/my-custom-endpoint'
   ```

## JSON-RPC Method mapping
In order to inject yours JSON-RPC method into the server add the tag `json_rpc_http_server.jsonrpc_method` and the key/value `method` like following example :
```yaml
services:
   method-a.service-id:
      class: Method\A\Class
      tags:
       - { name: 'json_rpc_http_server.jsonrpc_method', method: 'method-a' }
       - { name: 'json_rpc_http_server.jsonrpc_method', method: 'method-a-alias' }
```

   
## Method resolver

A method resolver is required, you can either use [`yoanm/symfony-jsonrpc-server-psr11-resolver`](https://github.com/yoanm/symfony-jsonrpc-server-psr11-resolver) or write your own

In case you want to use your own, it will be automatically injected if you use the tag `json_rpc_http_server.method_resolver` :
```yaml
services:
  my.custom_method_resolver.service:
    class: Custom\Method\Resolver\Class
    tags: ['json_rpc_http_server.method_resolver']
```


## Contributing
See [contributing note](./CONTRIBUTING.md)
