<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Controller\Api;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Vendor\CustomOrderProcessing\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class OrderStatusUpdate implements HttpPostActionInterface, OrderStatusUpdateInterface
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    protected OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param JsonFactory $resultJsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestInterface $request
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        OrderRepositoryInterface $orderRepository,
        RequestInterface $request,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->logger = $logger;
    }

    /**
     * Execute controller action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $requestContent = $this->request->getContent();
            $data = json_decode($requestContent, true);

            $orderIncrementId = $data['order_increment_id'] ?? null;
            $newStatus = $data['new_status'] ?? null;

            if (empty($orderIncrementId) || empty($newStatus)) {
                throw new LocalizedException(__('Order increment ID and new status are required.'));
            }

            $orderStatusHistory = $this->updateStatus($orderIncrementId, $newStatus);

            return $result->setData([
                'success' => true,
                'message' => __('Order status updated successfully.'),
                'order_status_history' => $orderStatusHistory
            ]);
        } catch (LocalizedException $e) {
            $this->logger->error('Localized Exception: ' . $e->getMessage(), ['exception' => $e]);
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (NoSuchEntityException $e) {
            $this->logger->error('No Such Entity Exception: ' . $e->getMessage(), ['exception' => $e]);
            return $result->setData([
                'success' => false,
                'message' => __('Order not found.')
            ]);
        } catch (\Exception $e) {
            $this->logger->critical('Unexpected Exception: ' . $e->getMessage(), ['exception' => $e]);
            return $result->setData([
                'success' => false,
                'message' => __('Unexpected error occurred: %1', $e->getMessage())
            ]);
        }
    }

    /**
     * Update order status by increment ID
     *
     * @param string $orderIncrementId
     * @param string $newStatus
     * @return OrderStatusHistoryInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateStatus(string $orderIncrementId, string $newStatus): OrderStatusHistoryInterface
    {
        // Fetch the order
        $order = $this->orderRepository->get($orderIncrementId);

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order not found.'));
        }

        // Update the order status
        $order->setState(Order::STATE_PROCESSING)
            ->setStatus($newStatus);
        $this->orderRepository->save($order);

        // Create and return OrderStatusHistory
        return $this->createOrderStatusHistory($order, $newStatus);
    }

    /**
     * Create and return OrderStatusHistory
     *
     * @param Order $order
     * @param string $newStatus
     * @return OrderStatusHistoryInterface
     */
    private function createOrderStatusHistory(Order $order, string $newStatus): OrderStatusHistoryInterface
    {
        /** @var OrderStatusHistoryInterface $orderStatusHistory */
        $orderStatusHistory = $this->orderStatusHistoryRepository->create();
        $orderStatusHistory->setOrderId($order->getId())
            ->setOldStatus($order->getStatus())
            ->setNewStatus($newStatus)
            ->setCreatedAt(date('Y-m-d H:i:s'));

        // Save the order status history
        $this->orderStatusHistoryRepository->save($orderStatusHistory);

        return $orderStatusHistory;
    }
}
