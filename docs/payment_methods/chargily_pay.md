# Payment Method: Chargily Pay

## Create payment

```php

$credentials = new Credentials();

$credentials->setApiKey("");// Api Key
$credentials->setSecretKey("");// Secret Key

$attributes = new Attributes();

$attributes->setOrderId("");//Order/Invoice ID
$attributes->setClientFullName("");//Client Fullname/username
$attributes->setClientEmail("");//client email
$attributes->setAmount("");//Amount
$attributes->setDiscount("");//Discount
$attributes->setMethod("");//DAHABIA or CIB
$attributes->setDescription("");//Order Description
$attributes->setBackUrl("");//Back Url (Must be Active Url)
$attributes->setProcessUrl("");//Payment Processing Url (Must be Active Url)


$payment = Pay::createPayment(Pay::PM_CHARGILY_PAY, $credentials, $attributes);



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

$credentials->setApiKey("");
$credentials->setSecretKey("");

$attributes = new Attributes();

$payment = Pay::processPayment(Pay::PM_CHARGILY_PAY, $credentials, $attributes);

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
