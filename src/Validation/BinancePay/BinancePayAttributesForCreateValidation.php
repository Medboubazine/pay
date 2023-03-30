<?php

namespace Medboubazine\Pay\Validation\BinancePay;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class BinancePayAttributesForCreateValidation extends Validation implements ValidationInterface
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
            "currency" => "required|min:3|in:BUSD,USDT",
            "description" => "required|min:2|max:512",
            "process_url" => "required|url",
            "back_url" => "required|url",
        ];
    }
}
