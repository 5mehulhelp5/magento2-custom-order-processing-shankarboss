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

interface OrderStatusHistoryRepositoryInterface
{
    /**
     * Save order status history record.
     *
     * @param \Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface $history
     * @return \Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException If the save operation fails.
     */
    public function save(OrderStatusHistoryInterface $history): OrderStatusHistoryInterface;
}
