# Vendor_CustomOrderProcessing

A Magento 2 module for custom order processing with REST API, status logging, and email notifications.

## **Features**
1. **REST API** to update order status (`POST /V1/custom-order/update-status`).
2. **Event Observer** to log status changes in a custom table [vendor_order_status_history].
3. **Email Notification** when an order is shipped.

## **Installation**
1. Copy the module to `app/code/Vendor/CustomOrderProcessing/`.
2. Run:
   ```bash
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:flush

## **Final Notes**
**Magento 2.4.7 compatible**  
**PSR-4 & Dependency Injection**  
**REST API with OAuth**  
**Database logging**  
**Email notifications**