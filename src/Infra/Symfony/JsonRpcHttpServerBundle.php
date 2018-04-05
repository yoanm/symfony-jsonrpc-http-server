<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

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
