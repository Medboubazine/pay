# Payment Method: Paypal

## Create payment

```php

$credentials = new Credentials();

$credentials = new Credentials();

$credentials->setEnv(""); //sandbox OR live
$credentials->setApiKey(""); //Your paypal Api Key
$credentials->setSecretKey(""); //Your paypal Api Key

$attributes = new Attributes();

$attributes->setInvoiceId();
$attributes->setAmount("10");
$attributes->setCurrency("USD");
$attributes->setDescription("Order Amount");
$attributes->setProcessUrl("/dist/process.php"); // Payment process page
$attributes->setCancelUrl("/cancel.php");

$payment = Pay::createPayment(Pay::PM_PAYPAL, $credentials, $attributes);


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

$credentials = new Credentials();

$credentials->setEnv(""); //sandbox OR live
$credentials->setApiKey(""); //Your paypal Api Key
$credentials->setSecretKey(""); //Your paypal Api Key

$attributes = new Attributes();

$payment = Pay::processPayment(Pay::PM_PAYPAL, $credentials, $attributes);

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
