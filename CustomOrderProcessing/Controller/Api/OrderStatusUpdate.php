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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

class OrderStatusUpdate implements HttpPostActionInterface, OrderStatusUpdateInterface
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        protected JsonFactory $resultJsonFactory,
        protected OrderRepositoryInterface $orderRepository,
        protected RequestInterface $request
    ) {
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

            $this->updateStatus($orderIncrementId, $newStatus);

            return $result->setData([
                'success' => true,
                'message' => __('Order status updated successfully.')
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
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
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStatus($orderIncrementId, $newStatus)
    {
        if (empty($orderIncrementId) || empty($newStatus)) {
            throw new LocalizedException(__('Order increment ID and new status are required.'));
        }

        $order = $this->orderRepository->get($orderIncrementId);

        if (!$order->getId()) {
            throw new LocalizedException(__('Order not found.'));
        }

        $order->setState(Order::STATE_PROCESSING)
            ->setStatus($newStatus);
        $this->orderRepository->save($order);
    }
}
