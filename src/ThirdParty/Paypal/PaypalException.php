<?php

namespace Medboubazine\Pay\ThirdParty\Paypal;

use Exception;

final class PaypalException extends Exception
{
    public static function error($message, $code = 1)
    {
        throw new self($message, $code);
    }
    public static function createOrderError(array $content, $code = 1)
    {
        $message = "INVALID REQUEST: ";
        foreach ($content["details"] as $value) {
            $field = ((isset($value['field'])) ? " : {$value['field']}" : "-");
            $description = ((isset($value['description'])) ? " : {$value['description']}" : "");
            $issue = ((isset($value['issue'])) ? " : {$value['issue']}" : "");

            $message .= "{$field} => ({$issue})" . $description . " ||| \n\r";
        }
        throw new self($message, $code);
    }
}
