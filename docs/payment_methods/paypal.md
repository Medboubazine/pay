# Payment Method: Paypal

## Create payment

```php

$credentials = new Credentials();

$credentials->setEnv("sandbox"); //sandbox OR live
$credentials->setApiKey(""); //Your paypal Api Key
$credentials->setSecretKey(""); //Your paypal Api Key
$credentials->setLogEnabled(false);
//if logEnabled set is true uncoment next line
//$credentials->setLogPath(__DIR__ . "/log/paypal_log.log");

$attributes = new Attributes();

$attributes->setAmount("10");
$attributes->setCurrency("USD");
$attributes->setDescription("Order Amount");
$attributes->setProcessUrl("/dist/process.php"); // Payment process page
$attributes->setBackUrl("/back.php");

$payment = Pay::createPayment(Pay::PM_PAYPAL, $credentials, $attributes);

if ($payment) {
    $payment_id = $payment->getId();
    $url = $payment->getId();
    //redirect to url
} else {
    // "Payment creation failed
}

```

## Process payment

```php
$pay = new Pay();

$credentials = new Credentials();

$credentials->setEnv("");
$credentials->setApiKey("");
$credentials->setSecretKey("");
$credentials->setLogEnabled(false);
$credentials->setLogPath(__DIR__ . "/log/paypal_log.log");

$attributes = new Attributes();
$attributes->setAcceptOnlyVerifiedAccounts(false);

$payment = Pay::processPayment(Pay::PM_PAYPAL, $credentials, $attributes);

if($payment){
    if($payment->getStatus() === "approved"){
    //payment is confirmed
    }elseif($payment->getStatus() === "canceled"){
        //payment is canceled
    }else($payment->getStatus() === "failed"){
        //payment is failed
    }
}

```
