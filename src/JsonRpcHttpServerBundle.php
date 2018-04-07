<?php
namespace Yoanm\SymfonyJsonRpcHttpServer;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

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
