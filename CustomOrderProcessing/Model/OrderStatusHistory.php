<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class OrderStatusHistory
 *
 * Model for storing order status history records.
 */
class OrderStatusHistory extends AbstractModel implements OrderStatusHistoryInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct(): void
    {
        $this->_init(\Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory::class);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId): self
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     */
    public function getOldStatus(): ?string
    {
        return $this->getData(self::OLD_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setOldStatus($oldStatus): self
    {
        return $this->setData(self::OLD_STATUS, $oldStatus);
    }

    /**
     * @inheritdoc
     */
    public function getNewStatus(): string
    {
        return (string) $this->getData(self::NEW_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setNewStatus($newStatus): self
    {
        return $this->setData(self::NEW_STATUS, $newStatus);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
