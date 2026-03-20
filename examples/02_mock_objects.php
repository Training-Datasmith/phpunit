<?php

declare(strict_types=1);

/**
 * Example 2: Test doubles — mocks, stubs, and spies.
 *
 * This example shows how to isolate the class under test from its
 * collaborators using PHPUnit's built-in mock object generator.
 *
 * Run with:
 *   vendor/bin/phpunit examples/02_mock_objects.php
 */

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

// -----------------------------------------------------------------------
// Production code being tested
// -----------------------------------------------------------------------

interface PaymentGateway
{
    public function charge(string $customerId, int $amountCents): bool;
    public function refund(string $transactionId): bool;
}

interface OrderRepository
{
    public function save(Order $order): void;
    public function findById(int $id): ?Order;
}

final class Order
{
    public string $status = 'pending';

    public function __construct(
        public readonly int $id,
        public readonly string $customerId,
        public readonly int $amountCents,
    ) {}
}

final class OrderService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderRepository $repository,
    ) {}

    public function placeOrder(Order $order): bool
    {
        $charged = $this->gateway->charge($order->customerId, $order->amountCents);

        if ($charged) {
            $order->status = 'paid';
            $this->repository->save($order);
            return true;
        }

        $order->status = 'failed';
        return false;
    }
}

// -----------------------------------------------------------------------
// Tests
// -----------------------------------------------------------------------

final class OrderServiceTest extends TestCase
{
    /**
     * Stub: configure a return value; don't assert the call was made.
     */
    public function test_place_order_saves_when_payment_succeeds(): void
    {
        // createMock() returns a mock that stubs all methods to return null/false
        // by default. We override the specific method we care about.
        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->method('charge')->willReturn(true);

        $repository = $this->createMock(OrderRepository::class);
        // We want to assert save() is called exactly once.
        $repository->expects($this->once())->method('save');

        $service = new OrderService($gateway, $repository);
        $order   = new Order(1, 'customer-abc', 1000);

        $result = $service->placeOrder($order);

        $this->assertTrue($result);
        $this->assertSame('paid', $order->status);
    }

    /**
     * Mock: assert the method is called with specific arguments.
     */
    public function test_gateway_is_charged_with_correct_amount(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);

        // expects() verifies the call happened AND with the right arguments.
        $gateway->expects($this->once())
            ->method('charge')
            ->with(
                $this->equalTo('customer-xyz'),
                $this->equalTo(5000),
            )
            ->willReturn(true);

        $repository = $this->createStub(OrderRepository::class);

        $service = new OrderService($gateway, $repository);
        $order   = new Order(2, 'customer-xyz', 5000);

        $service->placeOrder($order);
        // The expectation on $gateway is verified automatically after the test.
    }

    /**
     * Stub: payment fails — verify the order status reflects the failure.
     */
    public function test_order_status_is_failed_when_payment_is_declined(): void
    {
        $gateway = $this->createStub(PaymentGateway::class);
        $gateway->method('charge')->willReturn(false);

        $repository = $this->createMock(OrderRepository::class);
        // save() must NOT be called when payment fails.
        $repository->expects($this->never())->method('save');

        $service = new OrderService($gateway, $repository);
        $order   = new Order(3, 'customer-123', 2500);

        $result = $service->placeOrder($order);

        $this->assertFalse($result);
        $this->assertSame('failed', $order->status);
    }

    /**
     * Consecutive calls: different return values on successive invocations.
     */
    public function test_retry_logic_with_consecutive_return_values(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->method('charge')
            ->willReturnOnConsecutiveCalls(false, true);

        $repository = $this->createStub(OrderRepository::class);

        $service = new OrderService($gateway, $repository);
        $order   = new Order(4, 'customer-999', 100);

        // First attempt fails.
        $this->assertFalse($service->placeOrder($order));

        // Reset status to allow a second attempt.
        $order->status = 'pending';

        // Second attempt succeeds.
        $this->assertTrue($service->placeOrder($order));
    }
}
