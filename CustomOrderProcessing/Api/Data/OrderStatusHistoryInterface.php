<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Api\Data;

interface OrderStatusHistoryInterface
{
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const OLD_STATUS = 'old_status';
    public const NEW_STATUS = 'new_status';
    public const CREATED_AT = 'created_at';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get Old Status
     *
     * @return string|null
     */
    public function getOldStatus();

    /**
     * Set Old Status
     *
     * @param string $oldStatus
     * @return $this
     */
    public function setOldStatus($oldStatus);

    /**
     * Get New Status
     *
     * @return string
     */
    public function getNewStatus();

    /**
     * Set New Status
     *
     * @param string $newStatus
     * @return $this
     */
    public function setNewStatus($newStatus);

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
