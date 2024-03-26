<?php

namespace Medboubazine\Pay\Validation\Paysera;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Helpers\Countries;
use Medboubazine\Pay\Core\Helpers\Currencies;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PayseraAttributesForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "back_url" => "required|url",
            "process_url" => "required|url",
            "country" => "required|in:" . implode(",", Countries::getCodes()),
            "order_id" => "required|numeric|min:1",
            "currency" => "required|in:" . implode(",", Currencies::getCodes()),
            "amount" => "required|numeric|min:0.01",
        ];
    }
}
