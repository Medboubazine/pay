<?php

namespace Medboubazine\Pay\Validation\Paypal;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PaypalAttributesForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "amount" => "required|numeric|min:0.01",
            "currency" => "required|min:3|in:AUD,BRL,CAD,CNY,CZK,DKK,EUR,HKD,HUF,JPY,MYR,MXN,TWD,NZD,NOK,PHP,PLN,RUB,SEK,GBP,THB,SGD,USD,CHF",
            "description" => "required|min:2|max:512",
            "process_url" => "required|url",
            "cancel_url" => "required|url",
        ];
    }
}
