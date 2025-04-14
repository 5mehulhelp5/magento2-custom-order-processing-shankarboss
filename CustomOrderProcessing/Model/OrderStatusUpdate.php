<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Sales\Model\Order\InvoiceService;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\TransactionFactory;

class OrderStatusUpdate implements OrderStatusUpdateInterface
{
    /**
     * Constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CollectionFactory $orderCollectionFactory
     * @param LoggerInterface $logger
     * @param OrderStatusHistoryInterface $responseFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param InvoiceResource $invoiceResource
     * @param ShipmentDocumentFactory $shipmentFactory
     * @param ShipmentResource $shipmentResource
     * @param ItemFactory $shipmentItemFactory
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CollectionFactory $orderCollectionFactory,
        protected LoggerInterface $logger,
        protected OrderStatusHistoryInterface $responseFactory,
        protected \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        protected InvoiceResource $invoiceResource,
        protected ShipmentDocumentFactory $shipmentFactory,
        protected ShipmentResource $shipmentResource,
        protected ItemFactory $shipmentItemFactory,
        protected TransactionFactory $transactionFactory
    ) {
    }

    /**
     * Update order status
     *
     * @param string $orderIncrementId
     * @param string $newStatus
     * @return OrderStatusHistoryInterface
     */
    public function updateStatus($orderIncrementId, $newStatus): OrderStatusHistoryInterface
    {
        $this->logger->info("Received orderIncrementId: " . $orderIncrementId);
        $this->logger->info("Received newStatus: " . $newStatus);

        try {
            if (empty($orderIncrementId)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Order increment ID is required'));
            }

            $collection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('increment_id', trim($orderIncrementId))
                ->setPageSize(1);

            if ($collection->count() === 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Order not found'));
            }

            $order = $collection->getFirstItem();

            // Handle Invoice only if status is 'processing'
            if (in_array($newStatus, ['processing']) && $order->canInvoice()) {
                $this->logger->info("Preparing invoice for order: " . $orderIncrementId);

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();

                $order->setIsInProcess(true);
                $order->addStatusHistoryComment(__('Invoice generated via API.'));

                $transaction = $this->transactionFactory->create();
                $transaction->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
            }

            // Handle Shipment only if status is 'complete'
            if (in_array($newStatus, ['complete']) && $order->canShip()) {
                $this->logger->info("Preparing shipment for order: " . $orderIncrementId);

                $shipment = $this->shipmentFactory->create($order, []);
                $shipment->register();

                $shipment->getOrder()->setIsInProcess(true);
                $shipment->addComment(__('Shipment generated via API.'));

                $transaction = $this->transactionFactory->create();
                $transaction->addObject($shipment)
                            ->addObject($shipment->getOrder())
                            ->save();
            }

            // Update order status
            $order->setStatus($newStatus);
            $order->setIsInProcess(true);
            $order->addStatusHistoryComment(__('Order status updated via API to %1', $newStatus));

            $this->orderRepository->save($order);

            return $this->responseFactory->setData([
                'success' => true,
                'message' => __('Order status updated successfully.')->render(),
                'order_id' => $order->getId(),
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Order Status API Error: ' . $e->getMessage());

            return $this->responseFactory->setData([
                'success' => false,
                'message' => __('Error: %1', $e->getMessage())->render(),
                'order_id' => null,
                'new_status' => null
            ]);
        }
    }
}
