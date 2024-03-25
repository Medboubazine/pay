# Payment Method: Chargily Pay

## Create payment

```php

$credentials = new Credentials();

$credentials->setEnv("sandbox");
$credentials->setPublicKey($key);
$credentials->setSecretKey($secret);


$attributes = new Attributes();

$attributes->setOrderId("abcedf");

$attributes->setClientFullName("MR fullname");
$attributes->setClientEmail("mr@mail.com");
$attributes->setClientPhoneNumber("213790909090");
$attributes->setClientAddress([]);
$attributes->setClientMetadata([]);

$attributes->setLocale("en");
$attributes->setDescription("This order description");
$attributes->setAmount(1230);
$attributes->setCurrency("DZD");
$attributes->setMetadata([]);

$attributes->setBackUrl("");
$attributes->setProcessUrl("");


$payment = Pay::createPayment(Pay::PM_CHARGILY_PAY_V2, $credentials, $attributes);


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

$credentials = new Credentials();

$credentials->setEnv("sandbox");
$credentials->setPublicKey($key);
$credentials->setSecretKey($secret);


$attributes = new Attributes();


$payment = Pay::processPayment(Pay::PM_CHARGILY_PAY_V2, $credentials, $attributes);

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
