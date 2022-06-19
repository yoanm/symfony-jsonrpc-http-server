<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Event;

use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\EventDispatcher\Event;
use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;

/**
 * Class SymfonyJsonRpcServerEvent
 */
class SymfonyJsonRpcServerEvent extends Event
{
    /** @var JsonRpcServerEvent */
    private $jsonRpcServerEvent;

    /**
     * @param JsonRpcServerEvent $jsonRpcServerEvent
     */
    public function __construct(JsonRpcServerEvent $jsonRpcServerEvent)
    {
        $this->jsonRpcServerEvent = $jsonRpcServerEvent;
    }

    /**
     * @return JsonRpcServerEvent
     */
    public function getJsonRpcServerEvent() : JsonRpcServerEvent
    {
        return $this->jsonRpcServerEvent;
    }
}
