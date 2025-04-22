<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Api;

use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Magento\Framework\Exception\LocalizedException;

interface OrderStatusUpdateInterface
{
    /**
     * Update order status.
     *
     * @param string $orderIncrementId The increment ID of the order.
     * @param string $newStatus The new status to set for the order.
     * @return \Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException If the order doesn't exist or cannot be updated.
     */
    public function updateStatus(string $orderIncrementId, string $newStatus): OrderStatusHistoryInterface;
}
