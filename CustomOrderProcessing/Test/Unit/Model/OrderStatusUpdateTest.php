<?php
declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Vendor\CustomOrderProcessing\Model\OrderStatusUpdate;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;

/**
 * Unit test for OrderStatusUpdate model
 */
class OrderStatusUpdateTest extends TestCase
{
    /**
     * Test successful order status update
     */
    public function testUpdateStatusSuccess(): void
    {
        $orderId = 123;
        $status = 'complete';

        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();

        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $orderRepository->expects($this->once())
            ->method('save')
            ->with($order)
            ->willReturn(true);

        $model = new OrderStatusUpdate(
            $orderRepository,
            $this->createMock(OrderCollectionFactory::class),
            $this->createMock(InvoiceService::class),
            $this->createMock(InvoiceResource::class),
            $this->createMock(ShipmentDocumentFactory::class),
            $this->createMock(ShipmentResource::class)
        );

        $result = $model->updateStatus($orderId, $status);

        $this->assertTrue($result);
    }

    /**
     * Test exception thrown if order not found
     */
    public function testUpdateStatusThrowsException(): void
    {
        $orderId = 999;

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Order not found');

        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willThrowException(
                new LocalizedException(__('Order not found'))
            );

        $model = new OrderStatusUpdate(
            $orderRepository,
            $this->createMock(OrderCollectionFactory::class),
            $this->createMock(InvoiceService::class),
            $this->createMock(InvoiceResource::class),
            $this->createMock(ShipmentDocumentFactory::class),
            $this->createMock(ShipmentResource::class)
        );

        $model->updateStatus($orderId, 'complete');
    }
}
