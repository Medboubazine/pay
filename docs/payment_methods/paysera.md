# Payment Method: Paysera

## Create payment

```php
$pay = new Pay();

$credentials = new Credentials();

$credentials->setEnv("");//'sandbox' for testing Or 'live' for production
$credentials->setProjectId("");//Project Id
$credentials->setSignPassword("");//Sign password

$attributes = new Attributes();

$attributes->setOrderId("");//Order/Invoice Number
$attributes->setAmount("");//Amount
$attributes->setCurrency("");//Currency
$attributes->setCountry("");//Country
$attributes->setBackUrl("");//Back Url
$attributes->setProcessUrl("");//Payment Processing url


$payment = Pay::createPayment(Pay::PM_PAYSERA, $credentials, $attributes);

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

$credentials->setProjectId("");
$credentials->setSignPassword("");

$attributes = new Attributes();

$attributes->setAllowTestPayments(false);
$attributes->setAcceptOnlyMacroPayments(true);

$payment = Pay::processPayment(Pay::PM_PAYSERA, $credentials, $attributes);


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
