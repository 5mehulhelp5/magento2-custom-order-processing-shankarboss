<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\OrderStatusHistoryRepositoryInterface;
use Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory as ResourceModel;
use Magento\Framework\Exception\CouldNotSaveException;

class OrderStatusHistoryRepository implements OrderStatusHistoryRepositoryInterface
{
    /**
     * OrderStatusHistory
     *
     * @var Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory $resource
     */
    public function __construct(ResourceModel $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Save method
     *
     * @param Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface $history
     * @return void
     */
    public function save(\Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface $history)
    {
        try {
            $this->resource->save($history);
            return $history;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }
}
