<?php
declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Test\Unit\Model;

use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Vendor\CustomOrderProcessing\Api\OrderStatusHistoryRepositoryInterface;
use Vendor\CustomOrderProcessing\Exception\OrderStatusUpdateException;
use Vendor\CustomOrderProcessing\Model\OrderStatusHistoryFactory;
use Vendor\CustomOrderProcessing\Model\OrderStatusUpdate;

class OrderStatusUpdateTest extends TestCase
{
    /** @var OrderStatusUpdate */
    private OrderStatusUpdate $model;

    /** @var MockObject */
    private MockObject $orderRepositoryMock;

    /** @var MockObject */
    private MockObject $orderCollectionFactoryMock;

    /** @var MockObject */
    private MockObject $loggerMock;

    /** @var MockObject */
    private MockObject $responseMock;

    /** @var MockObject */
    private MockObject $invoiceServiceMock;

    /** @var MockObject */
    private MockObject $invoiceResourceMock;

    /** @var MockObject */
    private MockObject $shipmentFactoryMock;

    /** @var MockObject */
    private MockObject $shipmentResourceMock;

    /** @var MockObject */
    private MockObject $shipmentItemFactoryMock;

    /** @var MockObject */
    private MockObject $transactionFactoryMock;

    /** @var MockObject */
    private MockObject $creditmemoServiceMock;

    /** @var MockObject */
    private MockObject $creditmemoFactoryMock;

    /** @var MockObject */
    private MockObject $creditmemoManagementMock;

    /** @var MockObject */
    private MockObject $orderStatusHistoryFactoryMock;

    /** @var MockObject */
    private MockObject $orderStatusHistoryRepositoryMock;

    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->orderCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->responseMock = $this->getMockBuilder(OrderStatusHistoryInterface::class)
            ->addMethods(['setData'])
            ->getMockForAbstractClass();
        $this->invoiceServiceMock = $this->createMock(InvoiceService::class);
        $this->invoiceResourceMock = $this->createMock(InvoiceResource::class);
        $this->shipmentFactoryMock = $this->createMock(ShipmentDocumentFactory::class);
        $this->shipmentResourceMock = $this->createMock(ShipmentResource::class);
        $this->shipmentItemFactoryMock = $this->createMock(ItemFactory::class);
        $this->transactionFactoryMock = $this->createMock(TransactionFactory::class);
        $this->creditmemoServiceMock = $this->createMock(CreditmemoService::class);
        $this->creditmemoFactoryMock = $this->createMock(CreditmemoFactory::class);
        $this->creditmemoManagementMock = $this->createMock(CreditmemoManagementInterface::class);
        $this->orderStatusHistoryFactoryMock = $this->createMock(OrderStatusHistoryFactory::class);
        $this->orderStatusHistoryRepositoryMock = $this->createMock(OrderStatusHistoryRepositoryInterface::class);

        $this->model = new OrderStatusUpdate(
            $this->orderRepositoryMock,
            $this->orderCollectionFactoryMock,
            $this->loggerMock,
            $this->responseMock,
            $this->invoiceServiceMock,
            $this->invoiceResourceMock,
            $this->shipmentFactoryMock,
            $this->shipmentResourceMock,
            $this->shipmentItemFactoryMock,
            $this->transactionFactoryMock,
            $this->creditmemoServiceMock,
            $this->creditmemoFactoryMock,
            $this->creditmemoManagementMock,
            $this->orderStatusHistoryFactoryMock,
            $this->orderStatusHistoryRepositoryMock
        );
    }

    public function testUpdateStatusWithEmptyOrderId(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Order increment ID is required.');
        $this->model->updateStatus('', 'processing');
    }

    public function testUpdateStatusWithOrderNotFound(): void
    {
        $orderIncrementId = '000000036';

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('addFieldToFilter')->willReturnSelf();
        $collectionMock->method('setPageSize')->willReturnSelf();
        $collectionMock->method('getSize')->willReturn(0);

        $this->orderCollectionFactoryMock->method('create')->willReturn($collectionMock);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Order not found.');
        $this->model->updateStatus($orderIncrementId, 'processing');
    }

    public function testUpdateStatusToProcessingWithInvoice(): void
    {
        $orderIncrementId = '000000042';
        $newStatus = OrderStatusUpdate::STATUS_PROCESSING;
        $oldStatus = 'pending';

        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->exactly(2))->method('getStatus')
        ->willReturnOnConsecutiveCalls($oldStatus, $newStatus);
        $orderMock->method('canInvoice')->willReturn(true);
        $orderMock->method('getId')->willReturn(42);
        $orderMock->method('getData')->willReturn([
            'entity_id' => '000000042',
            'status' => $oldStatus,
            'increment_id' => $orderIncrementId
        ]);

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('addFieldToFilter')->with('increment_id', $orderIncrementId)->willReturnSelf();
        $collectionMock->method('setPageSize')->with(1)->willReturnSelf();
        $collectionMock->method('getSize')->willReturn(1);
        $collectionMock->method('getFirstItem')->willReturn($orderMock);

        $this->orderCollectionFactoryMock->method('create')->willReturn($collectionMock);

        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceMock->method('getTotalQty')->willReturn(1);
        $invoiceMock->method('getOrder')->willReturn($orderMock);

        $this->invoiceServiceMock->method('prepareInvoice')->with($orderMock)->willReturn($invoiceMock);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->exactly(2))->method('addObject')
        ->withConsecutive([$invoiceMock], [$orderMock])->willReturnSelf();
        $transactionMock->expects($this->once())->method('save')->willReturnSelf();

        $this->transactionFactoryMock->method('create')->willReturn($transactionMock);

        $historyMock = $this->createMock(\Vendor\CustomOrderProcessing\Model\OrderStatusHistory::class);
        $historyMock->method('setOrderId')->with(42)->willReturnSelf();
        $historyMock->method('setOldStatus')->with($oldStatus)->willReturnSelf();
        $historyMock->method('setNewStatus')->with($newStatus)->willReturnSelf();
        $historyMock->method('getOldStatus')->willReturn($oldStatus);

        $this->orderStatusHistoryFactoryMock->method('create')->willReturn($historyMock);
        $this->orderStatusHistoryRepositoryMock->expects($this->once())->method('save')->with($historyMock);

        $this->orderRepositoryMock->expects($this->once())->method('save')->with($orderMock);

        $this->responseMock->expects($this->once())->method('setData')->with([
            'success' => true,
            'message' => __('Order status updated successfully.')->render(),
            'order_id' => 42,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ])->willReturnSelf();

        $result = $this->model->updateStatus($orderIncrementId, $newStatus);

        $this->assertSame($this->responseMock, $result);
    }

    public function testUpdateStatusToCompleteWithShipment(): void
    {
        $orderIncrementId = '000000036';
        $newStatus = OrderStatusUpdate::STATUS_COMPLETE;
        $oldStatus = 'processing';

        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->exactly(2))->method('getStatus')
        ->willReturnOnConsecutiveCalls($oldStatus, $newStatus);
        $orderMock->method('canShip')->willReturn(true);
        $orderMock->method('getId')->willReturn(36);
        $orderMock->method('getData')->willReturn([
            'entity_id' => '000000036',
            'status' => $oldStatus,
            'increment_id' => $orderIncrementId
        ]);

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('addFieldToFilter')->with('increment_id', $orderIncrementId)->willReturnSelf();
        $collectionMock->method('setPageSize')->with(1)->willReturnSelf();
        $collectionMock->method('getSize')->willReturn(1);
        $collectionMock->method('getFirstItem')->willReturn($orderMock);

        $this->orderCollectionFactoryMock->method('create')->willReturn($collectionMock);

        $shipmentMock = $this->createMock(Shipment::class);
        $shipmentMock->method('register')->willReturnSelf();
        $shipmentMock->method('getOrder')->willReturn($orderMock);

        $this->shipmentFactoryMock->method('create')->willReturn($shipmentMock);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->exactly(2))->method('addObject')
        ->withConsecutive([$shipmentMock], [$orderMock])->willReturnSelf();
        $transactionMock->expects($this->once())->method('save')->willReturnSelf();

        $this->transactionFactoryMock->method('create')->willReturn($transactionMock);

        $historyMock = $this->createMock(\Vendor\CustomOrderProcessing\Model\OrderStatusHistory::class);
        $historyMock->method('setOrderId')->with(36)->willReturnSelf();
        $historyMock->method('setOldStatus')->with($oldStatus)->willReturnSelf();
        $historyMock->method('setNewStatus')->with($newStatus)->willReturnSelf();
        $historyMock->method('getOldStatus')->willReturn($oldStatus);

        $this->orderStatusHistoryFactoryMock->method('create')->willReturn($historyMock);
        $this->orderStatusHistoryRepositoryMock->method('save')->with($historyMock);

        $this->orderRepositoryMock->method('save')->with($orderMock);

        $this->responseMock->expects($this->once())->method('setData')->with([
            'success' => true,
            'message' => __('Order status updated successfully.')->render(),
            'order_id' => 36,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ])->willReturnSelf();

        $result = $this->model->updateStatus($orderIncrementId, $newStatus);

        $this->assertSame($this->responseMock, $result);
    }

    public function testUpdateStatusWithException(): void
    {
        $orderIncrementId = '000000036';
        $newStatus = 'processing';

        $this->orderCollectionFactoryMock->method('create')->willThrowException(new \Exception('Test exception'));

        $this->expectException(OrderStatusUpdateException::class);
        $this->expectExceptionMessage('Unable to update order status. Please check the logs for more details.');

        $this->model->updateStatus($orderIncrementId, $newStatus);
    }
}
