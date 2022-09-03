<?php
namespace Tests\Functional\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\SymfonyJsonRpcHttpServer\Event\SymfonyJsonRpcServerEvent;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Event\SymfonyJsonRpcServerEvent
 */
class SymfonyJsonRpcServerEventTest extends TestCase
{
    use ProphecyTrait;

    /** @var JsonRpcServerEvent|ObjectProphecy */
    private $jsonRpcServerEvent;
    /** @var SymfonyJsonRpcServerEvent */
    private $event;

    protected function setUp(): void
    {
        $this->jsonRpcServerEvent = $this->prophesize(JsonRpcServerEvent::class);

        $this->event = new SymfonyJsonRpcServerEvent(
            $this->jsonRpcServerEvent->reveal()
        );
    }

    public function testShouldReturnTheSdkEvent()
    {
        $this->assertSame(
            $this->jsonRpcServerEvent->reveal(),
            $this->event->getJsonRpcServerEvent()
        );
    }
}
