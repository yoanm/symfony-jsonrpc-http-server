<?php
namespace Tests\Functional\BehatContext\App;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;

class JsonRpcMethod implements JsonRpcMethodInterface
{
    public function validateParams(array $paramList)
    {
    }

    public function apply(array $paramList = null)
    {
        return 'OK';
    }
}
