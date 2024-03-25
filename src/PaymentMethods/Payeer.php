<?php

namespace Medboubazine\Pay\PaymentMethods;

use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\ThirdParty\Payeer\Payeer as ThirdPartyPayeer;
use Medboubazine\Pay\ThirdParty\Payeer\PayeerMethod;
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

        $third_party = new ThirdPartyPayeer($credentials->getMerchantId(), $credentials->getSecretKey(), $credentials->getEncryptionKey());

        $url = $third_party->getCheckoutUrl(
            $attributes->getOrderId(),
            $attributes->getAmount(),
            $attributes->getCurrency(),
            $attributes->getDescription(),
            $attributes->getBackUrl(),
            $attributes->getProcessUrl(),
            PayeerMethod::PAYEER
        );

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

        $third_party = new ThirdPartyPayeer($credentials->getMerchantId(), $credentials->getSecretKey(), $credentials->getEncryptionKey());

        $webhook_data = $third_party->parseWebhook();

        if (is_array($webhook_data)) {

            $status = PaymentStatus::payeer($webhook_data['status']);

            return (new Payment())
                //REQUIRED
                ->setId($webhook_data['id'])
                ->setOrderId($webhook_data['order'])
                ->setStatus($status)
                ->setPayerFirstName(null)
                ->setPayerLastName(null)
                ->setPayerFullName(null)
                ->setPayerEmail($webhook_data['client_email'])
                ->setAmount($webhook_data['amount'])
                ->setFee(0)
                ->setTotal($webhook_data['amount'])
                ->setCurrency($webhook_data['currency']);
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
