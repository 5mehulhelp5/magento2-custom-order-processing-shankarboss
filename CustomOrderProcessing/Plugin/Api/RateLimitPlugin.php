<?php
/**
 * Order Status Update Model
 *
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Plugin\Api;

use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Vendor\CustomOrderProcessing\Helper\RateLimiter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;

class RateLimitPlugin
{
    /**
     * Constructor.
     *
     * @param RateLimiter $rateLimiter
     * @param State $appState
     * @return void
     */
    public function __construct(
        protected RateLimiter $rateLimiter,
        protected State $appState
    ) {
    }

    /**
     * Plugin before method for OrderStatusUpdate::updateStatus.
     *
     * Allows modifying or validating input before the status update is executed.
     *
     * @param OrderStatusUpdateInterface $subject
     * @param string $orderIncrementId
     * @param string $newStatus
     * @return array
     */
    public function beforeUpdateStatus(
        OrderStatusUpdateInterface $subject,
        string $orderIncrementId,
        string $newStatus
    ): array {
        // Skip rate limit check in CLI integration test mode
        if (PHP_SAPI === 'cli' && $this->appState->getMode() === State::MODE_DEVELOPER) {
            return [$orderIncrementId, $newStatus];
        }

        $identifier = $this->rateLimiter->getClientIp();

        if ($this->rateLimiter->isRateLimited($identifier)) {
            throw new LocalizedException(__('Rate limit exceeded. Try again later.'));
        }

        return [$orderIncrementId, $newStatus];
    }
}
