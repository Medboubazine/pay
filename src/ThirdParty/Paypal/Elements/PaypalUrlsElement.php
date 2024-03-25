<?php

namespace Medboubazine\Pay\ThirdParty\Paypal\Elements;

use Medboubazine\Pay\ThirdParty\Paypal\Abstracts\ElementsAbstract;

class PaypalUrlsElement extends ElementsAbstract
{
    /**
     * Create
     *
     * @param string $id
     * @param string $product_name
     * @param string $description
     * @return self
     */
    public static function create(string $return_url, string $cancel_url)
    {
        $object = new self;

        return $object->setReturnUrl($return_url)
            ->setCancelUrl($cancel_url);
    }
}
