<?php
/**
 * Order Status Update Model
 *
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Model;

use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Service\CreditmemoService;
use Psr\Log\LoggerInterface;
use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Vendor\CustomOrderProcessing\Exception\OrderStatusUpdateException;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Vendor\CustomOrderProcessing\Model\OrderStatusHistoryFactory;
use Vendor\CustomOrderProcessing\Api\OrderStatusHistoryRepositoryInterface;

class OrderStatusUpdate implements OrderStatusUpdateInterface
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETE   = 'complete';
    public const STATUS_CLOSED     = 'closed';

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CollectionFactory $orderCollectionFactory
     * @param LoggerInterface $logger
     * @param OrderStatusHistoryInterface $responseFactory
     * @param InvoiceService $invoiceService
     * @param InvoiceResource $invoiceResource
     * @param ShipmentDocumentFactory $shipmentFactory
     * @param ShipmentResource $shipmentResource
     * @param ItemFactory $shipmentItemFactory
     * @param TransactionFactory $transactionFactory
     * @param CreditmemoService $creditmemoService
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoManagementInterface $creditmemoManagement
     * @param OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     */
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CollectionFactory $orderCollectionFactory,
        protected LoggerInterface $logger,
        protected OrderStatusHistoryInterface $responseFactory,
        protected InvoiceService $invoiceService,
        protected InvoiceResource $invoiceResource,
        protected ShipmentDocumentFactory $shipmentFactory,
        protected ShipmentResource $shipmentResource,
        protected ItemFactory $shipmentItemFactory,
        protected TransactionFactory $transactionFactory,
        protected CreditmemoService $creditmemoService,
        protected CreditmemoFactory $creditmemoFactory,
        protected CreditmemoManagementInterface $creditmemoManagement,
        protected OrderStatusHistoryFactory $orderStatusHistoryFactory,
        protected OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
    ) {
    }

    /**
     * Update order status and generate invoice, shipment, or credit memo as needed.
     *
     * @param string $orderIncrementId
     * @param string $newStatus
     * @return OrderStatusHistoryInterface
     * @throws OrderStatusUpdateException|LocalizedException
     */
    public function updateStatus(string $orderIncrementId, string $newStatus): OrderStatusHistoryInterface
    {
        //echo "orderIncrementId : ".$orderIncrementId;echo "newStatus : ".$newStatus;die;
        $this->logger->info('Order status update initiated', [
            'order_increment_id' => $orderIncrementId,
            'new_status' => $newStatus
        ]);

        $orderIncrementId = trim($orderIncrementId);
        if ($orderIncrementId === '') {
            throw new LocalizedException(__('Order increment ID is required.'));
        }

        $validStatuses = [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETE,
            self::STATUS_CLOSED
        ];
        
        if (!in_array($newStatus, $validStatuses, true)) {
            throw new OrderStatusUpdateException(__('Unable to update order status.'));
        }

        try {
            $collection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('increment_id', $orderIncrementId)
                ->setPageSize(1);

            if ($collection->getSize() === 0) {
                throw new LocalizedException(__('Order not found.'));
            }

            /** @var Order $order */
            $order = $collection->getFirstItem();
            $oldStatus = $order->getStatus();

            if ($newStatus === self::STATUS_PROCESSING && $order->canInvoice()) {
                $this->generateInvoice($order);
            }

            if ($newStatus === self::STATUS_COMPLETE && $order->canShip()) {
                $this->generateShipment($order);
            }

            if ($newStatus === self::STATUS_CLOSED && $order->canCreditmemo()) {
                $this->generateCreditmemo($order);
            }

            $order->setStatus($newStatus);
            $order->setIsInProcess(true);
            $order->addStatusHistoryComment(__('Order status updated via API to "%1".', $newStatus));
            $this->orderRepository->save($order);

            $historyModel = $this->orderStatusHistoryFactory->create();
            $historyModel->setOrderId((int) $order->getId());
            $historyModel->setOldStatus($oldStatus);
            $historyModel->setNewStatus($newStatus);
            $this->orderStatusHistoryRepository->save($historyModel);

            return $this->responseFactory->setData([
                'success'    => true,
                'message'    => __('Order status updated successfully.')->render(),
                'order_id'   => (int)$order->getId(),
                'old_status' => $historyModel->getOldStatus(),
                'new_status' => $order->getStatus()
            ]);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error('Order status update failed', [
                'exception' => $e->getMessage(),
                'order_increment_id' => $orderIncrementId,
                'new_status' => $newStatus,
                'trace' => $e->getTraceAsString()
            ]);

            throw new OrderStatusUpdateException(
                __('Unable to update order status. Please check the logs for more details.')
            );
        }
    }

    /**
     * Generate and register an invoice for the given order.
     *
     * @param Order $order
     * @throws LocalizedException
     */
    private function generateInvoice(Order $order): void
    {
        $this->logger->info('Generating invoice for order', ['order_id' => $order->getId()]);

        $invoice = $this->invoiceService->prepareInvoice($order);
        if (!$invoice || !$invoice->getTotalQty()) {
            throw new LocalizedException(__('Cannot create an invoice without products.'));
        }

        $invoice->register();
        $order->setIsInProcess(true);
        $order->addStatusHistoryComment(__('Invoice generated via API.'));

        $transaction = $this->transactionFactory->create();
        $transaction->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
    }

    /**
     * Generate and register a shipment for the given order.
     *
     * @param Order $order
     * @throws LocalizedException
     */
    private function generateShipment(Order $order): void
    {
        $this->logger->info('Generating shipment for order', ['order_id' => $order->getId()]);

        $shipment = $this->shipmentFactory->create($order, []);
        if (!$shipment) {
            throw new LocalizedException(__('Cannot create shipment for this order.'));
        }

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        $shipment->addComment(__('Shipment generated via API.'));

        $transaction = $this->transactionFactory->create();
        $transaction->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
    }

    /**
     * Generate and refund a credit memo for the given order.
     *
     * @param Order $order
     * @throws LocalizedException
     */
    private function generateCreditmemo(Order $order): void
    {
        if (!$order->canCreditmemo()) {
            $this->logger->info("Order {$order->getIncrementId()} cannot be refunded.");
            return;
        }

        $creditmemo = $this->creditmemoFactory->createByOrder($order);
        if ($creditmemo->getGrandTotal() <= 0) {
            throw new LocalizedException(__('Credit memo total must be greater than zero.'));
        }

        $creditmemo->setOfflineRequested(true);
        $creditmemo->addComment(__('Credit memo generated via API.'));
        $creditmemo->getOrder()->setCustomerNoteNotify(false);
        $creditmemo->getOrder()->setIsInProcess(true);

        $this->creditmemoService->refund($creditmemo);
    }
}
