<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * ResourceModel for Order Status History
 */
class OrderStatusHistory extends AbstractDb
{
    /**
     * Initialize main table and primary key
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('vendor_order_status_history', 'entity_id');
    }
}
