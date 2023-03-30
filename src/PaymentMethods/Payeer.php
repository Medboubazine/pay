<?php

namespace Medboubazine\Pay\PaymentMethods;

use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\Validation\Payeer\PayeerAttributesForProcessValidation;
use Medboubazine\Pay\Validation\Payeer\PayeerCredentialsForProcessValidation;
use Medboubazine\Pay\Validation\Payeer\PayeerAttributesForCreateValidation;
use Medboubazine\Pay\Validation\Payeer\PayeerCredentialsForCreateValidation;

class Payeer extends PaymentMethod implements PaymentMethodInterface
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

        $f_name = $response['client'] ?? null;
        $l_name = null;
        $full_name = "{$f_name} {$l_name}";
        $status = PaymentStatus::payeer();

        return (new Payment())
            //REQUIRED
            ->setId()
            ->setOrderId()
            ->setStatus($status)
            ->setPayerFirstName($f_name)
            ->setPayerLastName($l_name)
            ->setPayerFullName($full_name)
            ->setAmount()
            ->setFee()
            ->setTotal()
            ->setCurrency();

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
                "credentials" => PayeerCredentialsForCreateValidation::class,
                "attributes" => PayeerAttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => PayeerCredentialsForProcessValidation::class,
                "attributes" => PayeerAttributesForProcessValidation::class,
            ],
        ];
    }
}
