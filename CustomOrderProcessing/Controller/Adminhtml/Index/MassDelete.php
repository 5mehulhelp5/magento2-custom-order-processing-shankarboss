<?php
declare(strict_types=1);

/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */

namespace Vendor\CustomOrderProcessing\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Vendor\CustomOrderProcessing\Model\ResourceModel\OrderStatusHistory\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class MassDelete
 * Handles mass deletion of order status history entries from admin grid.
 */
class MassDelete extends Action
{
    /**
     * Authorization resource for this action
     */
    public const ADMIN_RESOURCE = 'Vendor_CustomOrderProcessing::delete';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Execute method for mass deleting order status history entries
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            if ($collectionSize === 0) {
                $this->messageManager->addWarningMessage(__('No items selected for deletion.'));
                return $resultRedirect->setPath('*/*/');
            }

            foreach ($collection as $item) {
                $item->delete();
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } catch (LocalizedException $e) {
            $this->logger->warning('Mass Delete Warning: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting the selected items: %1', $e->getMessage())
            );
        } catch (\Throwable $e) {
            $this->logger->critical('Mass Delete Fatal Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->messageManager->addErrorMessage(
                __('An unexpected error occurred during mass deletion. Please try again.')
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
