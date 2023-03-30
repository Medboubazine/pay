<?php

namespace Medboubazine\Pay\PaymentMethods;

use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\Validation\Paysera\PayseraAttributesForCreateValidation;
use Medboubazine\Pay\Validation\Paysera\PayseraCredentialsForCreateValidation;
use Medboubazine\Pay\Validation\Paysera\PayseraAttributesForProcessValidation;
use Medboubazine\Pay\Validation\Paysera\PayseraCredentialsForProcessValidation;
use Throwable;
use WebToPay;

class Paysera extends PaymentMethod implements PaymentMethodInterface
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
        $configurations = [
            'projectid'     => $credentials->getProjectId(),
            'sign_password' => $credentials->getSignPassword(),
            'accepturl'     => $attributes->getBackUrl(),
            'cancelurl'     => $attributes->getBackUrl(),
            'callbackurl'   => $attributes->getProcessUrl(),
            'test'          => ($credentials->getEnv() === 'sandbox') ? 1 : 0,
            'country'       => $attributes->getCountry(),
        ];
        //
        $configurations['orderid'] = $attributes->getOrderId();
        $configurations['amount'] = ($attributes->getAmount() * 100);
        $configurations['currency'] = $attributes->getCurrency();
        //
        try {
            $url = WebToPay::getPaymentUrl() . "?" . \http_build_query(WebToPay::buildRequest($configurations));
            //
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return (new Payment)
                    ->setUrl($url)
                    ->setId($attributes->getOrderId());
            }
            //
        } catch (Throwable) {
        }
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
        //
        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                $credentials->getProjectId(),
                $credentials->getSignPassword(),
            );
            //
            if ($response['test'] === '1' and !$attributes->getAllowTestPayments()) {
                return null;
            }
            //
            if ($response['type'] !== 'macro' and $attributes->getAcceptOnlyMacroPayments()) {
                return null;
            }
            //
            $f_name = $response['name'] ?? null;
            $l_name = $response['surename'] ?? null;
            $full_name = "{$f_name} {$l_name}";
            $status = PaymentStatus::paysera($response['status']);

            return (new Payment)
                //REQUIRED
                ->setId($response['orderid'])
                ->setOrderId($response['orderid'])
                ->setStatus($status)
                ->setPayerFirstName($f_name)
                ->setPayerLastName($l_name)
                ->setPayerFullName($full_name)
                ->setAmount(strval($response['amount'] / 100))
                ->setFee("0.00")
                ->setTotal(strval($response['amount'] / 100))
                ->setCurrency($response['currency'])
                //OPTIONAL
                ->setCountry($response['country'])
                ->setMethod($response['payment'])
                ->setPayerEmail($response['p_email'])
                ->setPayerCountry($response['payer_ip_country'] ?? null)
                ->setPaymentCountry($response['payment_country'] ?? null);
        } catch (Throwable) {
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
                "credentials" => PayseraCredentialsForCreateValidation::class,
                "attributes" => PayseraAttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => PayseraCredentialsForProcessValidation::class,
                "attributes" => PayseraAttributesForProcessValidation::class,
            ],
        ];
    }
}
