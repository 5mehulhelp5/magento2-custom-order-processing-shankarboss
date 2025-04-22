<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('000000043');
$order->setState(\Magento\Sales\Model\Order::STATE_NEW);
$order->setStatus('pending');
$order->setStoreId(1);
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('fixture@example.com');
$order->setBillingAddress(
    $objectManager->create(\Magento\Sales\Model\Order\Address::class)->setData([
        'firstname'  => 'Test',
        'lastname'   => 'Customer',
        'street'     => ['123 Magento Lane'],
        'city'       => 'Test City',
        'postcode'   => '12345',
        'telephone'  => '1234567890',
        'country_id' => 'US'
    ])
);

// Add test product item
$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class)->setData([
    'product_id'    => 1,
    'product_type'  => 'simple',
    'name'          => 'Test Product',
    'sku'           => 'test-product',
    'qty_ordered'   => 1,
    'price'         => 10,
    'base_price'    => 10,
    'row_total'     => 10,
]);

$order->addItem($orderItem);

$order->setGrandTotal(10);
$order->setBaseGrandTotal(10);

// ✅ Add minimal required payment
$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo'); // 'checkmo' is a default offline payment method
$order->setPayment($payment);

$order->save();
