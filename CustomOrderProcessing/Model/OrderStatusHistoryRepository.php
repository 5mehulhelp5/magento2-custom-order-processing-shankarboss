<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\OrderStatusHistoryRepositoryInterface;
use Vendor\CustomOrderProcessing\Api\Data\OrderStatusHistoryInterface;
use Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory as ResourceModel;
use Vendor\CustomOrderProcessing\Exception\OrderStatusHistorySaveException;
use Psr\Log\LoggerInterface;

class OrderStatusHistoryRepository implements OrderStatusHistoryRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected ResourceModel $resource;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param ResourceModel $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceModel $resource,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * Save order status history record
     *
     * @param OrderStatusHistoryInterface $history
     * @return OrderStatusHistoryInterface
     * @throws OrderStatusHistorySaveException
     */
    public function save(OrderStatusHistoryInterface $history): OrderStatusHistoryInterface
    {
        try {
            $this->resource->save($history);
        } catch (\Exception $e) {
            $this->logger->error('OrderStatusHistory save failed', [
                'exception' => $e,
                'data' => $history->getData()
            ]);
            throw new OrderStatusHistorySaveException(__('Unable to save order status history.'));
        }

        return $history;
    }
}
