<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Api;

use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Magento\Framework\Exception\LocalizedException;

interface OrderStatusUpdateInterface
{
    /**
     * Update order status
     *
     * @api
     *
     * @param string $orderIncrementId
     * @param string $newStatus
     * @return \Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface
     * @throws LocalizedException
     */
    public function updateStatus($orderIncrementId, $newStatus);
}
