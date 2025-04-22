<?php
namespace Vendor\CustomOrderProcessing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Psr\Log\LoggerInterface;
use Vendor\CustomOrderProcessing\Logger\RateLimitLogger;

class RateLimiter extends AbstractHelper
{
    public const CACHE_TAG = 'custom_order_rate_limit';
    public const XML_PATH_ENABLED = 'custom_order_processing/rate_limit/enabled';
    public const XML_PATH_LIMIT = 'custom_order_processing/rate_limit/limit_count';
    public const XML_PATH_TTL = 'custom_order_processing/rate_limit/limit_ttl';
    public const XML_PATH_WHITELIST = 'custom_order_processing/rate_limit/whitelisted_ips';

    /**
     * Constructor
     *
     * Initializes dependencies for rate limiting logic, including cache, request handling,
     * system and custom logging utilities.
     *
     * @param Context $context
     * @param CacheInterface $cache
     * @param Http $request
     * @param LoggerInterface $logger
     * @param RateLimitLogger $rateLimitLogger
     */
    public function __construct(
        Context $context,
        protected CacheInterface $cache,
        protected Http $request,
        protected LoggerInterface $logger,
        protected \Vendor\CustomOrderProcessing\Logger\RateLimitLogger $rateLimitLogger
    ) {
        parent::__construct($context);
    }

    /**
     * Check if the client is rate-limited
     *
     * @param string $identifier Client identifier (IP address)
     * @return bool
     */
    public function isRateLimited(string $identifier): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if ($this->isWhitelisted($identifier)) {
            return false;
        }

        $key = $this->getCacheKey($identifier);
        $count = (int) $this->cache->load($key);
        $limit = (int) $this->getLimit();

        if ($count >= $limit) {
            $message = "Rate limit exceeded for: $identifier";
            $this->logger->warning($message);
            $this->rateLimitLogger->warning($message); // Custom file log
            return true;
        }

        $this->notifyAdmin($identifier, $count, $limit);

        $this->increment($key, $count);
        return false;
    }

    /**
     * Notify admin when the rate limit is exceeded
     *
     * @param string $identifier
     * @param int $count
     * @param int $limit
     * @return void
     */
    private function notifyAdmin(string $identifier, int $count, int $limit): void
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $inbox = $objectManager->create(\Magento\AdminNotification\Model\Inbox::class);

            $inbox->addNotice(
                'Rate Limit Exceeded',
                __("The IP address %1 has exceeded the rate limit (%2/%3).", $identifier, $count, $limit)
            );
        } catch (\Exception $e) {
            $this->logger->error('Admin notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Increment the rate limit count
     *
     * @param string $key
     * @param int $current
     * @return void
     */
    private function increment(string $key, int $current): void
    {
        $ttl = $this->getTTL();
        $this->cache->save((string) ($current + 1), $key, [self::CACHE_TAG], $ttl);
    }

    /**
     * Get cache key based on client identifier
     *
     * Uses SHA-256 to generate a unique cache key.
     *
     * @param string $identifier
     * @return string
     */
    private function getCacheKey(string $identifier): string
    {
        return self::CACHE_TAG . '_' . hash('sha256', $identifier);
    }

    /**
     * Get rate limit from config
     *
     * @return int
     */
    private function getLimit(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_LIMIT);
    }

    /**
     * Get Time-To-Live (TTL) for cache from config
     *
     * @return int
     */
    private function getTTL(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_TTL);
    }

    /**
     * Check if rate limiting is enabled
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * Check if the client is in the whitelist
     *
     * @param string $identifier
     * @return bool
     */
    private function isWhitelisted(string $identifier): bool
    {
        $whitelist = $this->scopeConfig->getValue(self::XML_PATH_WHITELIST);
        $whitelistedIps = array_map('trim', explode(',', $whitelist ?? ''));

        return in_array($identifier, $whitelistedIps);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public function getClientIp(): string
    {
        return $this->request->getClientIp() ?? '127.0.0.1';
    }
}
