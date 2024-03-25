<?php

namespace Medboubazine\Pay\PaymentMethods;

use Chargily\ChargilyPay\Auth\Credentials as ChargilyAuthCredentials;
use Chargily\ChargilyPay\ChargilyPay as ChargilyChargilyPay;
use Chargily\ChargilyPay\Elements\CheckoutElement as ChargilyCheckoutElement;
use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Helpers\Str;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\Validation\ChargilyPayV2\ChargilyPayV2AttributesForCreateValidation;
use Medboubazine\Pay\Validation\ChargilyPayV2\ChargilyPayV2AttributesForProcessValidation;
use Medboubazine\Pay\Validation\ChargilyPayV2\ChargilyPayV2CredentialsForCreateValidation;
use Medboubazine\Pay\Validation\ChargilyPayV2\ChargilyPayV2CredentialsForProcessValidation;

class ChargilyPayV2 extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * Prepare chargily instance
     *
     * @return ChargilyChargilyPay
     */
    protected function getChargilyPayInstance(Credentials $credentials): ChargilyChargilyPay
    {
        return new ChargilyChargilyPay(new ChargilyAuthCredentials([
            "mode" => ($credentials->getEnv() == "sandbox") ? "test" : "live",
            "public" => $credentials->getPublicKey(),
            "secret" => $credentials->getSecretKey(),
        ]));
    }
    /**
     * create Payment Link
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public function createPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::createPayment($credentials, $attributes);
        //
        $chargily = $this->getChargilyPayInstance($credentials);

        //create customer
        $chargily_customer = $chargily->customers()->create([
            "name" => $attributes->getClientFullName(),
            "email" => $attributes->getClientEmail(),
            "phone" => $attributes->getClientPhoneNumber(),
            "metadata" => $attributes->getClientMetadata(),
        ]);
        if ($chargily_customer) {
            //
            $checkout = $chargily->checkouts()->create([
                "customer_id" => $chargily_customer->getId(),
                "locale" => $attributes->getLocale(),
                "description" => $attributes->getDescription(),
                "amount" => $attributes->getAmount(),
                "currency" => Str::lower($attributes->getCurrency()),
                "success_url" => $attributes->getBackUrl(),
                "failure_url" => $attributes->getBackUrl(),
                "webhook_endpoint" => $attributes->getProcessUrl(),
                "metadata" => [
                    "order_id" => $attributes->getOrderId(),
                    ...$attributes->getMetadata()
                ],

            ]);
            if ($checkout) {

                return (new Payment)
                    ->setId($checkout->getId())
                    ->setUrl($checkout->getUrl());
            }
        }
        return null;
    }
    /**
     * Process payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public function processPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::processPayment($credentials, $attributes);
        //
        $chargily = $this->getChargilyPayInstance($credentials);

        $webhook = $chargily->webhook()->get();

        $checkout = $webhook?->getData() ?? null;

        if ($checkout and $checkout instanceof ChargilyCheckoutElement) {

            $status = PaymentStatus::chargilyPayV2($checkout->getStatus());

            $charrgily_customer = null;
            if ($checkout->getCustomerId()) {
                $charrgily_customer = $chargily->customers()->get($checkout->getCustomerId());
            }

            return (new Payment())
                //REQUIRED
                ->setId($checkout->getId())
                ->setOrderId($checkout->getMetadata()['order_id'] ?? null)
                ->setStatus($status)
                ->setPayerFirstName($charrgily_customer?->getName())
                ->setPayerLastName(null)
                ->setPayerFullName($charrgily_customer?->getName())
                ->setAmount($checkout->getAmount())
                ->setFee($checkout->getFees())
                ->setTotal($checkout->getAmount())
                ->setCurrency(Str::upper($checkout->getCurrency()));
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
                "credentials" => ChargilyPayV2CredentialsForCreateValidation::class,
                "attributes" => ChargilyPayV2AttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => ChargilyPayV2CredentialsForProcessValidation::class,
                "attributes" => ChargilyPayV2AttributesForProcessValidation::class,
            ],
        ];
    }
}
