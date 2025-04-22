<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Api\Data;

/**
 * Interface for Order Status History data.
 */
interface OrderStatusHistoryInterface
{
    public const ORDER_ID    = 'order_id';
    public const OLD_STATUS  = 'old_status';
    public const NEW_STATUS  = 'new_status';
    public const CREATED_AT  = 'created_at';

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId(int $orderId): self;

    /**
     * Get Old Status
     *
     * @return string|null
     */
    public function getOldStatus(): ?string;

    /**
     * Set Old Status
     *
     * @param string $oldStatus
     * @return $this
     */
    public function setOldStatus(string $oldStatus): self;

    /**
     * Get New Status
     *
     * @return string
     */
    public function getNewStatus(): string;

    /**
     * Set New Status
     *
     * @param string $newStatus
     * @return $this
     */
    public function setNewStatus(string $newStatus): self;

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
