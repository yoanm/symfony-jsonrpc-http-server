<?php
namespace Tests\Common\Mock;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodParamsValidatorInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

class ConcreteParamsValidator implements JsonRpcMethodParamsValidatorInterface
{
    public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method): array
    {
        return [];
    }
}
