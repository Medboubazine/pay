<?php

namespace Medboubazine\Pay;

use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Exceptions\InvalidMethodException;
use Medboubazine\Pay\Core\Helpers\Str;
use Medboubazine\Pay\PaymentMethods\BinancePay;
use Medboubazine\Pay\PaymentMethods\ChargilyPay;
use Medboubazine\Pay\PaymentMethods\ChargilyPayV2;
use Medboubazine\Pay\PaymentMethods\Payeer;
use Medboubazine\Pay\PaymentMethods\Paypal;
use Medboubazine\Pay\PaymentMethods\Paysera;

class Pay
{

    /**
     * Payments Methods
     */
    public const PM_BINANCE_PAY = "binance_pay";
    public const PM_CHARGILY_PAY = "chargily_pay";
    public const PM_CHARGILY_PAY_V2 = "chargily_pay_v2";
    public const PM_PAYEER = "payeer";
    public const PM_PAYPAL = "paypal";
    public const PM_PAYSERA = "paysera";

    /**
     * Create Payment
     *
     * @param string $method
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public static function createPayment(string $method, Credentials $credentials, Attributes $attributes): ?Payment
    {
        if (!defined(self::class . "::PM_" . Str::upper($method))) {
            throw new InvalidMethodException("{$method} is invalid payment method");
        }

        $pm_methods = self::payment_methods();

        if (!in_array($method, array_keys($pm_methods))) {
            throw new InvalidMethodException("'{$method}' is  invalid payment method . Allowed methods is : " . implode(",", array_keys($pm_methods)));
        }

        $class = $pm_methods[$method];
        $pm = new $class;

        return $pm->createPayment($credentials, $attributes);
    }
    /**
     * process Payment
     *
     * @param string $method
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public static function processPayment(string $method, Credentials $credentials, Attributes $attributes): ?Payment
    {
        if (!defined(self::class . "::PM_" . Str::upper($method))) {
            throw new InvalidMethodException("{$method} is invalid payment method");
        }

        $pm_methods = self::payment_methods();

        if (!in_array($method, array_keys($pm_methods))) {
            throw new InvalidMethodException("'{$method}' is  invalid payment method . Allowed methods is : " . implode(",", array_keys($pm_methods)));
        }

        $class = $pm_methods[$method];
        $pm = new $class;

        return $pm->processPayment($credentials, $attributes);
    }

    /**
     * Payment methods
     *
     * @return array
     */
    public static function payment_methods(): array
    {
        return [
            "binance_pay" => BinancePay::class,
            "chargily_pay" => ChargilyPay::class,
            "chargily_pay_v2" => ChargilyPayV2::class,
            "payeer" => Payeer::class,
            "paypal" => Paypal::class,
            "paysera" => Paysera::class,
        ];
    }
}
