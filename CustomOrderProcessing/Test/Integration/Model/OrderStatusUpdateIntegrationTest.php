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

namespace Vendor\CustomOrderProcessing\Test\Integration\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Vendor\CustomOrderProcessing\Exception\OrderStatusUpdateException;
use Vendor\CustomOrderProcessing\Model\OrderStatusUpdate;

class OrderStatusUpdateIntegrationTest extends TestCase
{
    /**
     * @var OrderStatusUpdateInterface
     */
    private $orderStatusUpdate;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->orderStatusUpdate = $objectManager->get(OrderStatusUpdateInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Vendor_CustomOrderProcessing::Test/Integration/_files/order.php
     */
    public function testUpdateStatusToProcessingWithInvoice()
    {
        $order = Bootstrap::getObjectManager()->create(OrderInterface::class);
        $order->loadByIncrementId('000000043');

        $result = $this->orderStatusUpdate->updateStatus('000000043', OrderStatusUpdate::STATUS_PROCESSING);

        $this->assertInstanceOf(OrderStatusHistoryInterface::class, $result);
        $this->assertTrue($result->getSuccess());
        $this->assertEquals('processing', $result->getNewStatus());
    }

    public function testUpdateStatusWithEmptyOrderId()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Order increment ID is required.');

        $this->orderStatusUpdate->updateStatus('', 'processing');
    }

    public function testUpdateStatusWithOrderNotFound()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Order not found.');

        $this->orderStatusUpdate->updateStatus('999999999', 'processing');
    }

    /**
     * @magentoDataFixture Vendor_CustomOrderProcessing::Test/Integration/_files/order.php
     */
    public function testUpdateStatusWithInvalidStatus()
    {
        $this->expectException(OrderStatusUpdateException::class);
        $this->expectExceptionMessage('Unable to update order status.');

        $this->orderStatusUpdate->updateStatus('000000043', 'invalid_status');
    }
}
