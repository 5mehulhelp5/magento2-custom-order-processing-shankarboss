# Vendor_CustomOrderProcessing

A Magento 2 module for custom order processing with REST API, status logging, and email notifications.

## **Features**
1. **REST API** to update order status (`POST /V1/custom-order/update-status`).
2. **Event Observer** to log status changes in a custom table [vendor_order_status_history].
3. **Email Notification** when an order is shipped.
4. **Custom table creation for status history change** Status change history saving in custom table.
5. **Custom menu** custom menu with 2 child menus creation.
6. **Ratelimit set for API and it's admin configurations** API ratelimit with admin configurations.
7. **Admin grid with filters and bulk operation** grid with UI component filters and bulk operations.
8. **Unit test cases** Order process unit test cases.
9. **Integration test cases** Order process Integration test cases.

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
**Custom menu with proper ACL creation**
**Database logging**
**API ratelimit and it's configurations** 
**REST API with OAuth**
**Based on Order status change using API Invoice, shipment, creditmemo will generate** 
**Database logging**  
**Email notifications**
**Admin Grid with Bulk Operations**
**Rate Limit set for API Endpoint Call**
**Unit Tests**
**Integration Tests**