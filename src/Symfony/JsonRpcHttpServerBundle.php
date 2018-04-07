<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yoanm\SymfonyJsonRpcHttpServer\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * Class JsonRpcHttpServerBundle
 */
class JsonRpcHttpServerBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new JsonRpcHttpServerExtension(true);
    }
}
