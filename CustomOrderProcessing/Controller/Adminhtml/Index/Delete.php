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
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Vendor\CustomOrderProcessing\Model\OrderStatusHistoryFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Psr\Log\LoggerInterface;

/**
 * Admin controller for deleting an order status history record
 */
class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Vendor_CustomOrderProcessing::delete';

    /**
     * Constructor
     *
     * @param Context $context
     * @param OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param LoggerInterface $logger
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        Context $context,
        private readonly OrderStatusHistoryFactory $orderStatusHistoryFactory,
        private readonly LoggerInterface $logger,
        private readonly TypeListInterface $cacheTypeList,
        private readonly Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
    }

    /**
     * Execute delete action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid ID provided for deletion.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $model = $this->orderStatusHistoryFactory->create()->load($id);
            if (!$model->getId()) {
                throw new LocalizedException(__('Record not found.'));
            }

            $model->delete();

            // Optional: clear full_page cache (or specify custom one if needed)
            $this->cacheTypeList->cleanType('full_page');
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            $this->messageManager->addSuccessMessage(__('Record deleted successfully.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->warning('Delete Action Warning: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An unexpected error occurred while deleting the record.'));
            $this->logger->error('Delete Action Error: ' . $e->getMessage(), ['exception' => $e]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
