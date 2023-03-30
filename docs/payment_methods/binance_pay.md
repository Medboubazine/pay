# Payment Method: Binance Pay

## Create payment

```php
$pay = new Pay();

$credentials = new Credentials();

$credentials->setEnv("");//'sandbox' for testing OR 'live' for production
$credentials->setApiKey("");// Api Key
$credentials->setSecretKey("");//Secret Key
$credentials->setPaymentExpirationTime(60 * 30); //Paymen Expiration in seconds

$attributes = new Attributes();

$attributes->setOrderId("");//Order ID Must Be UNIQUE
$attributes->setAmount("");//Amount
$attributes->setCurrency("");//BUSD or USDT
$attributes->setDescription("");//Order Description
$attributes->setBackUrl("");//Back Url (Must be Active Url)
$attributes->setProcessUrl("");//Payment Processing Url (Must be Active Url)


$payment = Pay::createPayment(Pay::PM_BINANCE_PAY, $credentials, $attributes);


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

$credentials->setApiKey("");
$credentials->setSecretKey("");

$attributes = new Attributes();


$payment = Pay::processPayment(Pay::PM_BINANCE_PAY, $credentials, $attributes);

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
