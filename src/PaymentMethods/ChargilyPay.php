<?php

namespace Medboubazine\Pay\PaymentMethods;

use Medboubazine\Chargily\Chargily;
use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\Validation\ChargilyPay\ChargilyPayAttributesForCreateValidation;
use Medboubazine\Pay\Validation\ChargilyPay\ChargilyPayAttributesForProcessValidation;
use Medboubazine\Pay\Validation\ChargilyPay\ChargilyPayCredentialsForCreateValidation;
use Medboubazine\Pay\Validation\ChargilyPay\ChargilyPayCredentialsForProcessValidation;
use Throwable;

class ChargilyPay extends PaymentMethod implements PaymentMethodInterface
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
        //
        $configurations = [
            'api_key' => $credentials->getApiKey(),
            'api_secret' => $credentials->getSecretKey(),
            'urls' => [
                'back_url' => $attributes->getBackUrl(),
                'webhook_url' => $attributes->getProcessUrl(),
            ],
            'mode' => $attributes->getMethod(),
            'payment' => [
                'number' => (string) $attributes->getOrderId(),
                'client_name' => $attributes->getClientFullName(),
                'client_email' => $attributes->getClientEmail(),
                'amount' => $attributes->getAmount(),
                'discount' => $attributes->getDiscount() ?? 0,
                'description' => $attributes->getDescription(),
            ],
            "options" => [
                "headers" => [],
                "timeout" => 20,
            ],
        ];
        $chargily = new Chargily($configurations);
        $url = $chargily->getRedirectUrl();

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return (new Payment)
                ->setId($attributes->getOrderId())
                ->setUrl($url);
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
            $chargily = new Chargily([
                'api_key' => $credentials->getApiKey(),
                'api_secret' => $credentials->getSecretKey(),
            ]);
            if ($chargily->checkResponse()) {
                $response = $chargily->getResponseDetails();
                $response = $response["invoice"];

                $f_name = $response['client'] ?? null;
                $l_name = null;
                $full_name = "{$f_name} {$l_name}";
                $status = PaymentStatus::chargilyPay($response['status']);

                return (new Payment())
                    //REQUIRED
                    ->setId($response['id'])
                    ->setOrderId($response['invoice_number'])
                    ->setStatus($status)
                    ->setPayerFirstName($f_name)
                    ->setPayerLastName($l_name)
                    ->setPayerFullName($full_name)
                    ->setAmount(strval($response['amount']))
                    ->setFee(strval($response['fee']))
                    ->setTotal(strval($response['due_amount'] / 100))
                    ->setCurrency("DZD")
                    //OPTIONAL
                    ->setPayerEmail($response['client_email'])
                    ->setDiscount($response['discount'])
                    ->setDescription($response['comment'])
                    ->setMode($response['mode'])
                    ->setNew($response['new'] == "1")
                    ->setToken($response['invoice_token']);
            }
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
                "credentials" => ChargilyPayCredentialsForCreateValidation::class,
                "attributes" => ChargilyPayAttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => ChargilyPayCredentialsForProcessValidation::class,
                "attributes" => ChargilyPayAttributesForProcessValidation::class,
            ],
        ];
    }
}
