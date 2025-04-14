<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Api;

use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;

interface OrderStatusHistoryRepositoryInterface
{
    /**
     * Save
     *
     * @api
     *
     * @param OrderStatusHistoryInterface $history
     * @return OrderStatusHistoryInterface
     */
    public function save(OrderStatusHistoryInterface $history);
}
