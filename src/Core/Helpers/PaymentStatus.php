<?php

namespace Medboubazine\Pay\Core\Helpers;

class PaymentStatus
{
    /**
     * Paypal
     *
     * @param string $status
     * @return string
     */
    public static function paypal($status): string
    {
        $status = strval($status);

        switch ($status) {
            case 'approved':
                return "paid";
                break;
            default:
                return $status;
                break;
        }
    }
    /**
     * Paysera
     *
     * @param string $status
     * @return string
     */
    public static function paysera($status): string
    {
        $status = strval($status);

        switch ($status) {
            case '0':
                return "not-executed";
                break;
            case '1':
                return "paid";
                break;
            case '2':
                return "accepted-but-not-executed";
                break;
            case '3':
                return "needs-verification";
                break;
            case '4':
                return "waiting-bank-confirmation";
                break;
            default:
                return "failed";
                break;
        }
    }
    /**
     * ChargilyPay
     *
     * @param string $status
     * @return string
     */
    public static function chargilyPay($status): string
    {
        $status = strval($status);

        switch ($status) {
            case 'paid':
                return "paid";
                break;
            case 'canceled':
                return "canceled";
                break;
            case 'failed':
                return "failed";
                break;
            default:
                return $status;
                break;
        }
    }
    /**
     * ChargilyPay
     *
     * @param string $status
     * @return string
     */
    public static function binancePay($status): string
    {
        $status = strval($status);

        switch ($status) {
            case 'PAID':
                return "paid";
                break;
            case 'CANCELED':
                return "canceled";
                break;
            case 'EXPIRED':
                return "failed";
                break;
            case 'ERROR':
                return "failed";
                break;
            case 'ERROR':
                return "failed";
                break;
            default:
                return Str::lower($status);
                break;
        }
    }
}
