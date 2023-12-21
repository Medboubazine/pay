# Get Started

```php
$live =  false;
$key = "";
$secret = "";

$paypal = new Paypal($live,$key ,$secret);

```

# Create Payment

```php

$paypal_payment = PaypalPaymentDetailsElement::create("Invoice number", "Product name", "Description");

$paypal_urls = PaypalUrlsElement::create("your process url", "your cancel url");

$order = $paypal->createOrder("5", "EUR", $paypal_payment, $paypal_urls);

$checkout_url = $paypal->getOrderCheckoutUrl($order->getId());

```

# Get Payment

```php

$order = $paypal->getOrder("order id here");

```

# Capture payment

```php

$execute = $paypal->captureOrder("order_id_here");

```

# Refund Payment

```php
$get = $paypal->getOrder("order id here");

$unit = $get->getUnits()->first();

$captures = $get->getCaptures();

$capture = $captures->first();

$refund = $paypal->refund($capture->getId(), "0.5", "EUR", "Note to Customer");

```
