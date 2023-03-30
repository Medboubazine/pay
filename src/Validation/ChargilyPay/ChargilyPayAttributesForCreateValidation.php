<?php

namespace Medboubazine\Pay\Validation\ChargilyPay;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class ChargilyPayAttributesForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "order_id" => "required|min:1",
            "client_full_name" => "required|min:1",
            "client_email" => "required|email|min:1",
            "amount" => "required|numeric|min:100",
            "discount" => "required|numeric|min:0|max:99",
            "description" => "required|min:1",
            "method" => "required|in:CIB,EDAHABIA",
            "back_url" => "required|url",
            "process_url" => "required|url",
        ];
    }
}
