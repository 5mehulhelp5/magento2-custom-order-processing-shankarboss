<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
namespace Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\CustomOrderProcessing\Model\OrderStatusHistory;
use Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory as OrderStatusHistoryResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            OrderStatusHistory::class,
            OrderStatusHistoryResource::class
        );
    }
}
