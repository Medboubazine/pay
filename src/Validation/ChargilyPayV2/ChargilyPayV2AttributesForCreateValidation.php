<?php

namespace Medboubazine\Pay\Validation\ChargilyPayV2;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class ChargilyPayV2AttributesForCreateValidation extends Validation implements ValidationInterface
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
            "client_phone_number" => "required|numeric|digits_between:8,15",
            "client_address" => "nullable|array",
            "client_metadata" => "nullable|array",
            "locale" => "required|min:2|max:2",
            "description" => "required|min:1",
            "amount" => "required|numeric",
            "currency" => "required|min:3|max:3",
            "metadata" => "nullable|array",
            "back_url" => "required|url",
            "process_url" => "required|url",
        ];
    }
}
