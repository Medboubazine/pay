<?php

namespace Medboubazine\Pay\PaymentMethods;

use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalPaymentDetailsElement;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalUrlsElement;
use Medboubazine\Pay\ThirdParty\Paypal\Paypal as ThirdPartyPaypal;
use Medboubazine\Pay\Validation\Paypal\PaypalAttributesForCreateValidation;
use Medboubazine\Pay\Validation\Paypal\PaypalAttributesForProcessValidation;
use Medboubazine\Pay\Validation\Paypal\PaypalCredentialsForCreateValidation;
use Medboubazine\Pay\Validation\Paypal\PaypalCredentialsForProcessValidation;


class Paypal extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * Create Payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment
     */
    public function createPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::createPayment($credentials, $attributes);
        //
        $paypal = new ThirdPartyPaypal($credentials->getEnv() === "live", $credentials->getApiKey(), $credentials->getSecretKey());

        $paypal_payment = PaypalPaymentDetailsElement::create($attributes->getInvoiceId(), $attributes->getDescription(), $attributes->getDescription());

        $paypal_urls = PaypalUrlsElement::create($attributes->getProcessUrl(), $attributes->getCancelUrl());

        $paypal_order = $paypal->createOrder($attributes->getAmount(), $attributes->getCurrency(), $paypal_payment, $paypal_urls);

        $url = $paypal_order->getCheckoutUrl();
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return (new Payment)
                ->setId($paypal_order->getId())
                ->setStatus($paypal_order->getStatus())
                ->setUrl($paypal_order->getCheckoutUrl());
        }

        return null;
    }
    /**
     * Process payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment
     */
    public function processPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::processPayment($credentials, $attributes);
        //
        $paypal_id = $_GET['token'] ?? null;
        $paypal_payer_id = $_GET['PayerID'] ?? null;
        //
        $paypal = new ThirdPartyPaypal($credentials->getEnv() === "live", $credentials->getApiKey(), $credentials->getSecretKey());
        //
        $order = $paypal->getOrder($paypal_id);
        if ($order) {
            if ($order->getStatus() === "APPROVED") {
                $capture = $paypal->captureOrder($paypal_id);
            }
            $order = $paypal->getOrder($paypal_id);
            if ($order->getStatus() === "COMPLETED") {
                $unit = $order->getUnits()?->first();
                $captures = $unit["captures"];
                $amount = "0.00";

                foreach ($captures as $capture) {
                    $amount += $capture->getAmount();
                    $currency = $capture->getCurrency();
                }
            }
            return (new Payment())
                //REQUIRED
                ->setId($order->getId())
                ->setInvoiceId($unit['invoice_id'] ?? $order->getId())
                ->setStatus(PaymentStatus::paypal($order->getStatus()))
                ->setPayerId($order->getPayerId())
                ->setPayerEmail($order->getPayerEmail())
                ->setPayerFullName($order->getPayerFullname())
                ->setAmount($amount ?? "0.00")
                ->setFee("0.00")
                ->setTotal($amount ?? "0.00")
                ->setCurrency($currency ?? false);
        }

        return null;
    }
    /**
     * Validations
     *
     * @return array
     */
    public function validation(): array
    {
        return [
            "create" => [
                "credentials" => PaypalCredentialsForCreateValidation::class,
                "attributes" => PaypalAttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => PaypalCredentialsForProcessValidation::class,
                "attributes" => PaypalAttributesForProcessValidation::class,
            ],
        ];
    }
}
