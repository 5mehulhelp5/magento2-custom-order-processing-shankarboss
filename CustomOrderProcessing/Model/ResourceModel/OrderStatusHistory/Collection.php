<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\CustomOrderProcessing\Model\OrderStatusHistory;
use Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory as OrderStatusHistoryResource;

/**
 * Collection class for Order Status History
 */
class Collection extends AbstractCollection
{
    /**
     * Primary ID field name
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            OrderStatusHistory::class,
            OrderStatusHistoryResource::class
        );
    }
}
