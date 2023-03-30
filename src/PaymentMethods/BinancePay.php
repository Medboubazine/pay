<?php

namespace Medboubazine\Pay\PaymentMethods;

use Carbon\Carbon;
use Medboubazine\BinancePay\Binance;
use Medboubazine\BinancePay\Core\Resources\Credentials as ResourcesCredentials;
use Medboubazine\BinancePay\Core\Resources\Order;
use Medboubazine\BinancePay\Core\Resources\Product;
use Medboubazine\BinancePay\Core\Resources\Urls;
use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Helpers\Str;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\Validation\BinancePay\BinancePayAttributesForProcessValidation;
use Medboubazine\Pay\Validation\BinancePay\BinancePayCredentialsForProcessValidation;
use Medboubazine\Pay\Validation\BinancePay\BinancePayAttributesForCreateValidation;
use Medboubazine\Pay\Validation\BinancePay\BinancePayCredentialsForCreateValidation;

class BinancePay extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * Undocumented function
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public function createPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::createPayment($credentials, $attributes);
        //
        $timestamp = str_replace(".0", "", Carbon::now()
            ->addSeconds($credentials->getPaymentExpirationTime())
            ->valueOf());
        //
        $binance_credentials = new ResourcesCredentials();
        $binance_credentials->setApiKey($credentials->getApiKey())
            ->setApiSecret($credentials->getSecretKey())
            ->setEnvTerminalType("WEB");
        //
        $order_id = ($credentials->getEnv() === "sandbox") ? "test{$attributes->getOrderId()}" : $attributes->getOrderId();

        $order = new Order();
        $order->setId($order_id)
            ->setAmount($attributes->getAmount())
            ->setCurrency($attributes->getCurrency())
            ->setAllowedCurrencies(["BUSD", "USDT", "BNB"])
            ->setExpireTime($timestamp);
        //
        $product = new Product;
        $product->setId(Str::slug($attributes->getDescription()))
            ->setType("02")
            ->setCategory("6000")
            ->setName($attributes->getDescription());
        //
        $urls = new Urls();
        $urls->setReturnUrl($attributes->getBackUrl())
            ->setCancelUrl($attributes->getBackUrl())
            ->setWebhookUrl($attributes->getProcessUrl());
        //
        $binance = new Binance();
        //create payment
        $binance_payment  = $binance->getCheckoutUrl($binance_credentials,  $order,  $product,  $urls);

        if ($binance_payment instanceof \Medboubazine\BinancePay\Core\PayPayment) {
            return (new Payment)
                ->setId($binance_payment->getPrepayId())
                ->setUrl($binance_payment->getCheckoutUrl());
        }

        //
        return null;
    }
    /**
     * Process payment
     *
     * @return Payment|null
     */
    public function processPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::processPayment($credentials, $attributes);

        $binance_credentials = new \Medboubazine\BinancePay\Core\Resources\Credentials();
        $binance_credentials->setApiKey($credentials->getApiKey())
            ->setApiSecret($credentials->getSecretKey())
            ->setEnvTerminalType("WEB");
        //
        $binance = new  \Medboubazine\BinancePay\Binance();
        //
        $webhook_status = $binance->checkWebhook($binance_credentials);
        $webhook_status_pay_id = $binance->getWebhookPayId();
        $binance_payment = $binance->getPayment($binance_credentials, $webhook_status_pay_id); //5059

        if ($webhook_status && $binance_payment) {
            $f_name = null;
            $l_name = null;
            $full_name = null;
            $status = PaymentStatus::binancePay($binance_payment->getStatus());
            //
            return (new Payment())
                //REQUIRED
                ->setId($binance_payment->getPrepayId())
                ->setOrderId($binance_payment->getId())
                ->setStatus($status)
                ->setPayerFirstName($f_name)
                ->setPayerLastName($l_name)
                ->setPayerFullName($full_name)
                ->setAmount($binance_payment->getAmount())
                ->setFee("0.00")
                ->setTotal($binance_payment->getAmount())
                ->setCurrency($binance_payment->getCurrency())
                //Optional
                ->setCreatedAt(Carbon::parse($binance_payment->getCreatedAt(), $binance_payment->getTimeZone()));
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
                "credentials" => BinancePayCredentialsForCreateValidation::class,
                "attributes" => BinancePayAttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => BinancePayCredentialsForProcessValidation::class,
                "attributes" => BinancePayAttributesForProcessValidation::class,
            ],
        ];
    }
}
