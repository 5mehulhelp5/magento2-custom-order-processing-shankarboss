<?php
declare(strict_types=1);

namespace Vendor\CustomOrderProcessing\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class TestRollbackTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_shipment_creditmemo.php
     */
    public function testDummy()
    {
        $this->assertTrue(true);
    }
}
