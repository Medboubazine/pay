<?php

namespace Medboubazine\Pay\Validation\Paypal;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PaypalAttributesForProcessValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "accept_only_verified_accounts" => "nullable|boolean",
        ];
    }
}
