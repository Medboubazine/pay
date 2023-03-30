<?php

namespace Medboubazine\Pay\Validation\ChargilyPay;

use Medboubazine\Pay\Core\Abstracts\Validation;
use Medboubazine\Pay\Core\Interfaces\ValidationInterface;

class ChargilyPayAttributesForProcessValidation extends Validation implements ValidationInterface
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
