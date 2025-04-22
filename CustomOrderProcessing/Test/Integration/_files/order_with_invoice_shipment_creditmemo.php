<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Creditmemo;

$objectManager = Bootstrap::getObjectManager();

// Create order
/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('000000043');
$order->setState(Order::STATE_PROCESSING)
    ->setStatus('processing')
    ->setStoreId(1)
    ->setSubtotal(100)
    ->setBaseSubtotal(100)
    ->setGrandTotal(100)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('test@example.com');

$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class)->setData([
    'firstname'  => 'Test',
    'lastname'   => 'Customer',
    'street'     => ['123 Test Lane'],
    'city'       => 'Test City',
    'postcode'   => '12345',
    'telephone'  => '1234567890',
    'country_id' => 'US',
]);
$order->setBillingAddress($billingAddress);

// Add payment
$order->setPayment($objectManager->create(\Magento\Sales\Model\Order\Payment::class)->setMethod('checkmo'));

// Add item
$item = $objectManager->create(\Magento\Sales\Model\Order\Item::class)->setData([
    'product_id'    => 1,
    'product_type'  => 'simple',
    'name'          => 'Test Product',
    'sku'           => 'test-product',
    'qty_ordered'   => 1,
    'price'         => 100,
    'base_price'    => 100,
    'row_total'     => 100,
]);
$order->addItem($item);
$order->save();

// Create invoice
/** @var Invoice $invoice */
$invoice = $objectManager->create(Invoice::class);
$invoice->setOrder($order)
    ->addItem(clone $item)
    ->setGrandTotal(100)
    ->setBaseGrandTotal(100)
    ->register()
    ->pay();
$invoice->save();

// Create shipment
/** @var Shipment $shipment */
$shipment = $objectManager->create(Shipment::class);
$shipment->setOrder($order)
    ->addItem(clone $item)
    ->setTotalQty(1)
    ->register();
$shipment->save();

// Create credit memo
/** @var Creditmemo $creditmemo */
$creditmemo = $objectManager->create(Creditmemo::class);
$creditmemo->setOrder($order)
    ->addItem(clone $item)
    ->setGrandTotal(100)
    ->setBaseGrandTotal(100)
    ->register();
$creditmemo->save();
