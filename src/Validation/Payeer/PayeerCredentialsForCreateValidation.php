<?php

namespace Medboubazine\Pay\Validation\Payeer;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PayeerCredentialsForCreateValidation extends Validation implements ValidationInterface
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
            "merchant_id" => "required|min:1",
            "secret_key" => "required|min:1",
            "encryption_key" => "required|min:1",
        ];
    }
}
