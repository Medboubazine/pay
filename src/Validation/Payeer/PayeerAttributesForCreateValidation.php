<?php

namespace Medboubazine\Pay\Validation\Payeer;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PayeerAttributesForCreateValidation extends Validation implements ValidationInterface
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
            "amount" => "required|numeric",
            "currency" => "required|min:3|max:3",
            "description" => "required|min:1",
            "back_url" => "required|url",
            "process_url" => "required|url",
        ];
    }
}
