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
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller for rendering the Order Status History grid page
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization resource for this controller
     */
    public const ADMIN_RESOURCE = 'Vendor_CustomOrderProcessing::order_status_history';

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Execute method for rendering the Order Status History grid
     *
     * @return Page
     */
    public function execute(): Page
    {
        try {
            /** @var Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Vendor_CustomOrderProcessing::menu');
            $resultPage->addBreadcrumb(__('Status History'), __('Status History'));
            $resultPage->getConfig()->getTitle()->prepend(__('Order Status History'));

            return $resultPage;
        } catch (\Throwable $e) {
            $this->logger->critical('Failed to render order status history page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->messageManager->addErrorMessage(__('Unable to load the page. Please try again later.'));
            return $this->_redirect('*/*/');
        }
    }
}
