<?php

namespace Medboubazine\Pay\Validation\Paypal;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PaypalCredentialsForProcessValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "api_key" => "required|min:32",
            "secret_key" => "required|min:32",
            "env" => "required|in:sandbox,live",
            "log_enabled" => "required|boolean",
            "log_path" => "required_if:log_enabled,true",
        ];
    }
}
