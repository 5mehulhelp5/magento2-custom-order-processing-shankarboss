<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Magento\Framework\Model\AbstractModel;

class OrderStatusHistory extends AbstractModel implements OrderStatusHistoryInterface
{
    /**
     * Initialize OrderStatusHistory model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory::class);
    }

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    
    /**
     * Set Entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }
    
    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Old Status
     *
     * @return string|null
     */
    public function getOldStatus()
    {
        return $this->getData(self::OLD_STATUS);
    }
    
    /**
     * Set Old Status
     *
     * @param string $oldStatus
     * @return $this
     */
    public function setOldStatus($oldStatus)
    {
        return $this->setData(self::OLD_STATUS, $oldStatus);
    }

    /**
     * Get New Status
     *
     * @return string
     */
    public function getNewStatus()
    {
        return $this->getData(self::NEW_STATUS);
    }
    
    /**
     * Set New Status
     *
     * @param string $newStatus
     * @return $this
     */
    public function setNewStatus($newStatus)
    {
        return $this->setData(self::NEW_STATUS, $newStatus);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
    
    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
