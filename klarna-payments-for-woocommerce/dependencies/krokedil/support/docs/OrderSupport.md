### Order Support documentation.

#### Using the API request.

Note: This is currently not enabled.

The order support functionality will add a rest api endpoint to the WordPress store with the endpoint `/wp-json/wc-krokedil/v1/support/order/{order_id}`. This endpoint will return the order details for the order with the id `{order_id}`.

Optionally two extra parameters can be sent to also include log entries for the order from any requests that are logged in our loggers based on the two parameters sent.
These parameters are `log_context` and `log_metadata`. The `log_context` parameter should be a string that matches the slug of the log files that the specific plugin uses to log requests. For example for Avarda Checkout, this would be `avarda_checkout`.

The `log_metadata` parameter should be a string that matches the metadata key from the order in WooCommerce that we want to search for in the logs to get the rows that are relevant for this order. One example for this would be the transaction or payment id for different plugins, for Avarda Checkout this would be `_wc_avarda_purchase_id`. This will then extract any log rows that have the value stored in the order for the key and add them to the response sent back.

Due to the amount of data that could be required to read when including logs like this, the request itself might take some time to finish. So a decent timeout for the request might be a good idea to have.

We are also using the namespace wc-krokedil for the endpoint, that way the endpoint will automatically fall under the WooCommerce REST API authentication and permissions, meaning the API is protected behind the basic auth that WooCommerce uses. This means for this endpoint to be usable by us for a merchant, they need to create API keys for us to use to authenticate with.

#### Without API requests.
The same code that generates the API response can also be used by plugins to automatically generate the text output. This can be used for example if we want to implement this with the support tab or beacon that we have in our settings page package.

To do this, simply create an instance of the `OrderSupport` class with the same properties as the API request for `log_context` and `log_metadata`. Then call the `export_orders` with a list of WooCommerce orders to generate the output for.

### Example usage.
```php
$order_support = new OrderSupport('avarda_checkout', '_wc_avarda_purchase_id');
$order_1 = wc_get_order(123);
$order_2 = wc_get_order(456);
$orders = [$order_1, $order_2];

echo wp_json_encode( $order_support->export_orders($orders) );
```

To export_orders you can pass either a single WooCommerce order, or an array of WooCommerce orders. If you pass multiple orders, the output will be an array of the order details for each order.
The log parsing has been optimized to only search the logs once, even if you pass multiple orders, so the performance should be good even if you pass a lot of orders.
