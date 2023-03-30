<?php

namespace Medboubazine\Pay\Validation\ChargilyPay;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class ChargilyPayCredentialsForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "api_key" => "required|min:16",
            "secret_key" => "required|min:16",
        ];
    }
}
