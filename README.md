# JSON-RPC server symfony plugin
 [![License](https://img.shields.io/github/license/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server) [![Code size](https://img.shields.io/github/languages/code-size/yoanm/symfony-jsonrpc-http-server.svg)](https://github.com/yoanm/symfony-jsonrpc-http-server) [![PHP Versions](https://img.shields.io/badge/php-7.0%20%2F%207.1%20%2F%207.2-8892BF.svg)](https://php.net/)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/badges/build.png?b=master)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yoanm/symfony-jsonrpc-http-server/?branch=master)

[![Travis Build Status](https://img.shields.io/travis/yoanm/symfony-jsonrpc-http-server/master.svg?label=travis)](https://travis-ci.org/yoanm/symfony-jsonrpc-http-server) [![Travis PHP versions](https://img.shields.io/travis/php-v/yoanm/symfony-jsonrpc-http-server.svg)](https://travis-ci.org/yoanm/symfony-jsonrpc-http-server)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server) [![Packagist PHP version](https://img.shields.io/packagist/php-v/yoanm/symfony-jsonrpc-http-server.svg)](https://packagist.org/packages/yoanm/symfony-jsonrpc-http-server)

Symfony JSON-RPC HTTP Server to convert an HTTP json-rpc request into HTTP json-rpc response

## How to use

Sdk requires only two things : 
 - A method resolver : must implement [MethodResolverInterface](./src/Domain/Model/MethodResolverInterface.php), resolving logic's is your own.
 - Methods : JsonRpc methods that implement [JsonRpcMethodInterface](./src/Domain/Model/JsonRpcMethodInterface.php)
 
:warning: No dependency injection is managed in this library 

### Example
#### JSON-RPC Method
```php
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;

class DummyMethod implements JsonRpcMethodInterface
{
    /**
     * @param array $paramList
     *
     * @throws \Exception
     */
    public function validateParams(array $paramList)
    {
        //If case your app require a specific param for instance
        if (!isset($paramList['my-required-key')) {
            throw new \Exception('"my-required-key" is a required key');
        }
    }

    /**
     * @param array|null $paramList
     * 
     * @return array|int|null
     */
    public function apply(array $paramList = null)
    {
        // Handle the request
        ...
        // Then return a result
        return [
            'status' => 'done',
        ];
        // Or
        return null;
        // Or
        return 12345;
    }
}
```
#### Array method resolver (simple example)
*You could take example on [the one used for behat tests](./features/bootstrap/App/BehatMethodResolver.php)*
```php
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

class ArrayMethodResolver implements MethodResolverInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList = [];

    /**
     * @param string $methodName
     *
     * @return JsonRpcMethodInterface
     *
     * @throws JsonRpcMethodNotFoundException
     */
    public function resolve(string $methodName) : JsonRpcMethodInterface
    {
        if (!isset($this->methodList[$methodName])) {
            throw new JsonRpcMethodNotFoundException($methodName);
        }

        return $this->methodList[$methodName];
    }

    /**
     * @param JsonRpcMethodInterface $method
     * @param string                 $methodName
     */
    public function addMethod(JsonRpcMethodInterface $method, string $methodName)
    {
        $this->methodList[$methodName] = $method;
    }
}
```

Then add your method to the resolver and create the endpoint : 
```php
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

$resolver = new ArrayMethodResolver();
$resolver->addMethod(
    'dummy-method'
    new DummyMethod()
);

$responseCreator = new ResponseCreator();

$endpoint = new JsonRpcEndpoint(
    new RawRequestSerializer(
        new RequestDenormalizer()
    ),
    new RequestHandler(
        new MethodManager(
            $resolver,
            new CustomExceptionCreator()
        ),
        $responseCreator
    ),
    new RawResponseSerializer(
        new ResponseNormalizer()
    ),
    $responseCreator
);
```

Once endpoint is ready, you can send it request string : 
```php
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

$requestString = <<<JSONRPC
{
    "jsonrpc": "2.0",
    "id": 1
    "method": "dummy-method",
    "params": {
        "my-required-key": "a-value"
    }
}
JSONRPC;

$responseString = $endpoint->index($requestString);
```

`$responseString` will be the following string depending of method returned value : 
 * ```json
   {"jsonrpc":"2.0","id":1,"result":{"status":"done"}}
   ```
 * ```json
   {"jsonrpc":"2.0","id":1,"result":null}
   ```

 * ```json
   {"jsonrpc":"2.0","id":1,"result":12345}
   ```

## Contributing
See [contributing note](./CONTRIBUTING.md)
