# Payment Method: Payeer

## Create payment

```php

$credentials = new Credentials();

$credentials->setEnv("sandbox");
$credentials->setMerchantId($merchant_id);
$credentials->setSecretKey($secret);
$credentials->setEncryptionKey($enc);

$attributes = new Attributes();

$attributes->setOrderId("");
$attributes->setAmount("10.00");
$attributes->setCurrency("EUR");
$attributes->setDescription("");
$attributes->setBackUrl("");
$attributes->setProcessUrl("");

$payment = Pay::createPayment(Pay::PM_PAYEER, $credentials, $attributes);

if ($payment) {
    $payment_id = $payment->getId();
    $url = $payment->getUrl();
    //redirect to url
} else {
    // "Payment creation failed
}

```

## Process payment

```php
$pay = new Pay();

$credentials = new Credentials();

$credentials->setEnv("sandbox");
$credentials->setMerchantId($merchant_id);
$credentials->setSecretKey($secret);
$credentials->setEncryptionKey($enc);

$attributes = new Attributes();

$payment = Pay::processPayment(Pay::PM_PAYEER, $credentials, $attributes);

if($payment){
    if($payment->getStatus() === "paid"){
    //payment is confirmed
    }elseif($payment->getStatus() === "canceled"){
        //payment is canceled
    }else($payment->getStatus() === "failed"){
        //payment is failed
    }
}

```
