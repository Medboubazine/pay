<?php

namespace Medboubazine\Pay\Validation\ChargilyPayV2;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class ChargilyPayV2CredentialsForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "env" => "required|in:sandbox,live",
            "public_key" => "required|min:16",
            "secret_key" => "required|min:16",
        ];
    }
}
