<?php
/**
 * @package     Vendor_CustomOrderProcessing
 * @author      Shankar Bolla
 * @license     Open Software License (OSL 3.0)
 * @email       bolla.shankar9@gmail.com
 */
declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * Handles module configuration
 */
class Config extends AbstractHelper
{
    public const XML_PATH_ENABLE_CACHE = 'vendor_customorderprocessing/cache_settings/enable_cache';
    public const XML_PATH_CACHE_LIFETIME = 'vendor_customorderprocessing/cache_settings/cache_lifetime';

    /**
     * Is caching enabled from admin config
     *
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_CACHE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get cache lifetime in seconds
     *
     * @return int
     */
    public function getCacheLifetime(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_CACHE_LIFETIME, ScopeInterface::SCOPE_STORE) ?: 3600;
    }
}
