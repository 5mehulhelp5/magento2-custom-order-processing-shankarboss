<?php
declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Test\Integration\Api;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class UpdateStatusTest extends TestCase
{
    /**
     * @var OrderStatusUpdateInterface
     */
    private OrderStatusUpdateInterface $orderStatusUpdate;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->orderStatusUpdate = $objectManager->get(OrderStatusUpdateInterface::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
    }

    public function testUpdateOrderStatus(): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->get(1);

        // Act: Update the order status
        $result = $this->orderStatusUpdate->updateStatus((int)$order->getEntityId(), 'complete');

        // Assert: Return is true
        $this->assertTrue($result, 'Order status update result should be true');

        // Assert: Status is updated in DB
        $updatedOrder = $this->orderRepository->get($order->getEntityId());
        $this->assertEquals(
            'complete',
            $updatedOrder->getStatus(),
            'Order status should be updated to "complete"'
        );
    }
}
