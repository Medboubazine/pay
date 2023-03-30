<?php

namespace Medboubazine\Pay\Validation\Paysera;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PayseraAttributesForProcessValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "allow_test_payments" => "required|boolean",
            "accept_only_macro_payments" => "required|boolean",
        ];
    }
}
