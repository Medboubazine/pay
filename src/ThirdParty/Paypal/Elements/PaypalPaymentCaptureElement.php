<?php

namespace Medboubazine\Pay\ThirdParty\Paypal\Elements;

use Medboubazine\Pay\ThirdParty\Paypal\Abstracts\ElementsAbstract;

class PaypalPaymentCaptureElement extends ElementsAbstract
{

    public static function create(string $id, string $product_name, string $description)
    {
        $object = new self;

        return $object->setId($id)
            ->setProductName($product_name)
            ->setDescription($description);
    }
}
