<?php

namespace Medboubazine\Pay\Validation\Paysera;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PayseraCredentialsForProcessValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "project_id" => "required|numeric|min:1",
            "sign_password" => "required|min:16",
        ];
    }
}
