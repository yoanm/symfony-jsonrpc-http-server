# Symfony JSON-RPC server

[![License](https://img.shields.io/github/license/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server)
[![Code size](https://img.shields.io/github/languages/code-size/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server)
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github\&repo=yoanm/symfony-jsonrpc-http-server)](https://dependabot.com)

[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/yoanm/symfony-jsonrpc-http-server.svg?label=Scrutinizer\&logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/build-status/master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/yoanm/symfony-jsonrpc-http-server/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/8f39424add044b43a70bdb238e2f48db)](https://www.codacy.com/gh/yoanm/symfony-jsonrpc-http-server/dashboard?utm_source=github.com\&utm_medium=referral\&utm_content=yoanm/symfony-jsonrpc-http-server\&utm_campaign=Badge_Grade)

[![CI](https://github.com/yoanm/symfony-jsonrpc-http-server/actions/workflows/CI.yml/badge.svg?branch=master)](https://github.com/yoanm/symfony-jsonrpc-http-server/actions/workflows/CI.yml)
[![codecov](https://codecov.io/gh/yoanm/symfony-jsonrpc-http-server/branch/master/graph/badge.svg?token=NHdwEBUFK5)](https://codecov.io/gh/yoanm/symfony-jsonrpc-http-server)
[![Symfony Versions](https://img.shields.io/badge/Symfony-v4.4%20%2F%20v5.4%2F%20v6.x-8892BF.svg?logo=github)](https://symfony.com/)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server)
[![Packagist PHP version](https://img.shields.io/packagist/php-v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server)

Symfony JSON-RPC HTTP Server to convert an HTTP json-rpc request into HTTP json-rpc response.

Symfony bundle for [`yoanm/jsonrpc-server-sdk`](https://github.com/yoanm/php-jsonrpc-server-sdk)

See [yoanm/symfony-jsonrpc-params-validator](https://github.com/yoanm/symfony-jsonrpc-params-validator) for params validation.

See [yoanm/symfony-jsonrpc-http-server-doc](https://github.com/yoanm/symfony-jsonrpc-http-server-doc) for documentation generation.

## Versions

*   Symfony v3/4 - PHP >=7.1 : `^2.0`

    ⚠️⚠️ `v2.1.0` and `v2.1.1` were badly taggued, used `v3.0.0` instead ! ⚠️⚠️

*   Symfony v4/5 - PHP >=7.2 : `~3.0.0`

*   Symfony v4/5 - PHP >=7.3 : `^3.1`

*   Symfony v4.4/5.4 - PHP ^8.0 : `^3.2`

*   Symfony v4.4/5.4/6.x - PHP ^8.0 : `^3.3`

## How to use

Once configured, your project is ready to handle HTTP `POST` request on `/json-rpc` endpoint.

See below how to configure it.

## Configuration

Bundle requires only one thing :

*   JSON-RPC Methods which are compatible with [`yoanm/jsonrpc-server-sdk`](https://raw.githubusercontent.com/yoanm/php-jsonrpc-server-sdk)

It comes with [built-in method resolver](./src/Resolver/MethodResolver.php) which use a [service locator](https://symfony.com/doc/3.4/service_container/service_subscribers_locators.html#defining-a-service-locator). Using a service locator allow to load (and so instanciate dependencies, dependencies of dependencies, etc) method only when required (usually only one method is required by request, except for batch requests which will load one or more methods).

*[Behat demo app configuration folders](./features/demo_app/) can be used as examples.*

*   Add the bundles in your `config/bundles.php` file:
    ```php
    // config/bundles.php
    return [
        ...
        Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
        Yoanm\SymfonyJsonRpcHttpServer\JsonRpcHttpServerBundle::class => ['all' => true],
        ...
    ];
    ```

*   Add the following in your routing configuration :
    ```yaml
    # config/routes.yaml
    json-rpc-endpoint:
      resource: '@JsonRpcHttpServerBundle/Resources/config/routing/endpoint.xml'
    ```

*   Add the following in your configuration :
    ```yaml
    # config/config.yaml
    framework:
      secret: '%env(APP_SECRET)%'

    json_rpc_http_server: ~
    # Or the following in case you want to customize endpoint path
    #json_rpc_http_server:
    #  endpoint: '/my-custom-endpoint' # Default to '/json-rpc'
    ```

### JSON-RPC Method mapping

In order to inject yours JSON-RPC method into the server add the tag `json_rpc_http_server.jsonrpc_method` and the key/value `method` like following example :

```yaml
services:
   method-a.service-id:
      class: Method\A\Class
      tags:
       - { name: 'json_rpc_http_server.jsonrpc_method', method: 'method-a' }
       - { name: 'json_rpc_http_server.jsonrpc_method', method: 'method-a-alias' }
```

### Methods mapping aware

In case you want to be aware of which methods are registered inside the JSON-RPC server, you can use the `json_rpc_http_server.method_aware`. Your class must implements `JsonRpcMethodAwareInterface`.

```php
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodAwareInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class MappingCollector implements JsonRpcMethodAwareInterface
{
  /** @var JsonRpcMethodInterface[] */
  private $mappingList = [];

  public function addJsonRpcMethod(string $methodName, JsonRpcMethodInterface $method): void
  {
    $this->mappingList[$methodName] = $method;
  }

  /**
   * @return JsonRpcMethodInterface[]
   */
  public function getMappingList() : array
  {
    return $this->mappingList;
  }
}
```

```yaml
mapping_aware_service:
  class: App\Collector\MappingCollector
  tags: ['json_rpc_http_server.method_aware']
```

## Debug mode

You can setup 'debug' mode for the JSON-RPC server, which allows display of verbose error information within the response JSON body.
This information contains actual exception class name, code, message and stack trace.

> Note: you should never enable 'debug' mode in 'production' environment, since it will expose vital internal information to the API consumer.

Configuration example:

```yaml
# file 'config/packages/json_rpc.yaml'
json_rpc_http_server:
  endpoint: '/json-rpc'
  debug:
    enabled: false
    max_trace_size: 10
    show_trace_arguments: true
    simplify_trace_arguments: true

when@dev:
  json_rpc_http_server:
    debug:
      enabled: true

when@test:
  json_rpc_http_server:
    debug:
      enabled: true
```

## Contributing

See [contributing note](./CONTRIBUTING.md)
