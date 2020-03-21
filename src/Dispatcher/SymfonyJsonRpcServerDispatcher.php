<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Dispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\SymfonyJsonRpcHttpServer\Event\SymfonyJsonRpcServerEvent;

/**
 * Class SymfonyJsonRpcServerDispatcher
 */
class SymfonyJsonRpcServerDispatcher implements JsonRpcServerDispatcherInterface
{
    /** @var EventDispatcherInterface */
    private $symfonyEventDispatcher;

    /**
     * @param EventDispatcherInterface $symfonyEventDispatcher
     */
    public function __construct(EventDispatcherInterface $symfonyEventDispatcher)
    {
        $this->symfonyEventDispatcher = $symfonyEventDispatcher;
    }

    /**
     * @param string $eventName
     * @param JsonRpcServerEvent $event
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null) : void
    {
        $this->symfonyEventDispatcher->dispatch(
            $event ? new SymfonyJsonRpcServerEvent($event) : new \stdClass(),
            $eventName
        );
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     * @param int      $priority Default to 0
     */
    public function addJsonRpcListener(string $eventName, $listener, $priority = 0) : void
    {
        $this->symfonyEventDispatcher->addListener($eventName, $listener, $priority);
    }
}
