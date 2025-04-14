<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Vendor\CustomOrderProcessing\Model\OrderStatusHistoryFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Vendor\CustomOrderProcessing\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class OrderStatusChangeObserver implements ObserverInterface
{
    /**
     * Constructor
     *
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        protected OrderStatusHistoryFactory $orderStatusHistoryFactory,
        protected TransportBuilder $transportBuilder,
        protected ScopeConfigInterface $scopeConfig,
        protected DateTime $dateTime,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Execute observer
     *
     * Triggered when the associated event is fired. Handles order status change,
     * logs the status update, and sends a shipped email if applicable.
     *
     * @param Observer $observer The event observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();

        $this->logger->info("Order Status Change - Old: $oldStatus, New: $newStatus");

        // Save status change to custom table
        $history = $this->orderStatusHistoryFactory->create();
        $history->setOrderId($order->getId())
            ->setOldStatus($oldStatus)
            ->setNewStatus($newStatus)
            ->setCreatedAt($this->dateTime->gmtDate());
        $this->orderStatusHistoryRepository->save($history);

        if ($newStatus === 'shipped') {
            $this->logger->info("Order marked as shipped. Sending email...");
            $this->sendShippedEmail($order);
        }
    }

    /**
     * Send "order shipped" email to the customer
     *
     * Uses email template defined in email_templates.xml to notify the customer
     * that their order has been shipped.
     *
     * @param Order $order The order object
     * @return void
     */
    protected function sendShippedEmail(Order $order): void
    {
        $storeId = $order->getStoreId();
        $customerEmail = $order->getCustomerEmail();
        $customerName = $order->getCustomerName();

        if (!$customerEmail || !$customerName) {
            $this->logger->error("Missing customer email or name.");
            return;
        }

        $templateVars = [
            'order' => $order,
            'customer_name' => $customerName,
        ];

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_shipped_email_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ])
                ->setTemplateVars($templateVars)
                ->setFrom('general')
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->logger->info("Shipped email sent successfully.");
        } catch (\Exception $e) {
            $this->logger->error("Failed to send shipped email: " . $e->getMessage());
        }
    }
}
