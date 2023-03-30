<?php

namespace Medboubazine\Pay\Core\Interfaces;

use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;

interface PaymentMethodInterface
{
    public function createPayment(Credentials $credentials, Attributes $attributes): ?Payment;
    public function processPayment(Credentials $credentials, Attributes $attributes): ?Payment;
    public function getProcessMessages(): array;
    public function setProcessMessages(string $key, string $value);
    public function setPayment(Payment $payment);
    public function getPayment(): ?Payment;
    public function validation(): array;
}
