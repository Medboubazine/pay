<?php

namespace Medboubazine\Pay\Core\Abstracts;

use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Exceptions\ValidationException;

abstract class PaymentMethod
{
    /**
     * Credentials
     *
     * @var Credentials
     */
    protected Credentials $credentials;
    /**
     * Attributes
     *
     * @var Attributes
     */
    protected Attributes $attributes;
    /**
     * Payment
     *
     * @var Payment
     */
    protected ?Payment $payment;

    /**
     * Process payment messages
     *
     * @var array
     */
    protected array $process_messages = [];
    /**
     * Constructor
     */
    public function __construct()
    {
    }
    /**
     * Create Payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public function createPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        $validations = [
            $this->validation()['create']['credentials'] => $credentials,
            $this->validation()['create']['attributes'] => $attributes,
        ];

        foreach ($validations as $class => $args) {
            $validation = new $class($args->all());
            if (!$validation->passed()) {
                throw new ValidationException("(" . substr(get_class($args), strrpos(get_class($args), '\\') + 1) . " is invalid) : " . json_encode($validation->errors()), 1);
            }
        }

        return null;
    }
    /**
     * Process Payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment|null
     */
    public function processPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        $validations = [
            $this->validation()['process']['credentials'] => $credentials,
            $this->validation()['process']['attributes'] => $attributes,
        ];

        foreach ($validations as $class => $args) {
            $validation = new $class($args->all());
            if (!$validation->passed()) {
                throw new ValidationException("(" . substr(get_class($args), strrpos(get_class($args), '\\') + 1) . " is invalid) : " . json_encode($validation->errors()), 1);
            }
        }
        return null;
    }
    /**
     * get process messages
     *
     * @return void
     */
    public function getProcessMessages(): array
    {
        return $this->process_messages;
    }
    /**
     * get process messages
     *
     * @return void
     */
    public function setProcessMessages(string $key, string $value)
    {
        $this->process_messages[$key] = $value;
        return $this;
    }
    /**
     * Set payment
     *
     * @param Payment $payment
     * @return self
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }
    /**
     * Get payment
     *
     * @return Payment
     */
    public function getPayment(): ?Payment
    {
        return $this->payment ?? null;
    }
    /**
     * Validations
     *
     * @return array
     */
    public function validation(): array
    {
        return [];
    }
}
