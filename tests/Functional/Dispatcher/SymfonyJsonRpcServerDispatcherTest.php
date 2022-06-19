<?php
namespace Tests\Functional\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\SymfonyJsonRpcHttpServer\Dispatcher\SymfonyJsonRpcServerDispatcher;
use Yoanm\SymfonyJsonRpcHttpServer\Event\SymfonyJsonRpcServerEvent;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Dispatcher\SymfonyJsonRpcServerDispatcher
 */
class SymfonyJsonRpcServerDispatcherTest extends TestCase
{
    use ProphecyTrait;

    /** @var SymfonyJsonRpcServerDispatcher */
    private $dispatcher;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $sfDispatcher;

    protected function setUp(): void
    {
        $this->sfDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->dispatcher = new SymfonyJsonRpcServerDispatcher(
            $this->sfDispatcher->reveal()
        );
    }

    public function testShouldManageListeners()
    {
        $eventName1 = 'event-name-1';
        $listener1 = function () {
        };
        $eventName2 = 'event-name-2';
        $listener2 = function () {
        };
        $priority2 = 100;
        $this->sfDispatcher->addListener($eventName1, $listener1, 0)
            ->shouldBeCalled()
        ;
        $this->sfDispatcher->addListener($eventName2, $listener2, $priority2)
            ->shouldBeCalled()
        ;

        $this->dispatcher->addJsonRpcListener($eventName1, $listener1);
        $this->dispatcher->addJsonRpcListener($eventName2, $listener2, $priority2);
    }

    public function testShouldDispatchEventsWrappedIntoSpecificClass()
    {
        $eventName = 'event-name';
        $event = $this->prophesize(JsonRpcServerEvent::class);

        $this->sfDispatcher
            ->dispatch(
                Argument::allOf(
                    Argument::type(SymfonyJsonRpcServerEvent::class),
                    Argument::which('getJsonRpcServerEvent', $event->reveal())
                ),
                $eventName
            )
            ->shouldBeCalled();

        $this->dispatcher->dispatchJsonRpcEvent($eventName, $event->reveal());
    }
}
