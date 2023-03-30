<?php

namespace Medboubazine\Pay\Validation\BinancePay;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class BinancePayCredentialsForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "env" => "required|in:sandbox,live",
            "api_key" => "required|min:16",
            "secret_key" => "required|min:16",
            "payment_expiration_time" => "required|integer",
        ];
    }
}
