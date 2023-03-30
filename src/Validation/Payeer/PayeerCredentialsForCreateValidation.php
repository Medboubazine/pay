<?php

namespace Medboubazine\Pay\Validation\Payeer;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class PayeerCredentialsForCreateValidation extends Validation implements ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
