<?php

namespace Medboubazine\Pay\ThirdParty\Paypal\Elements;

use Medboubazine\Pay\Core\Helpers\Str;

class PaypalPaymentRefundElement
{
    /**
     * Attributes
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Magic Call
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $f3_chars = Str::lower(Str::substr($name, 0, 3));
        if ($f3_chars == 'set') {
            $attribute_name = Str::snake(Str::substr($name, 3, 99));
            $this->attributes[$attribute_name] = ($arguments[0] ?? null);
            return $this;
        }
        if ($f3_chars == 'get') {
            $attribute_name = Str::snake(Str::substr($name, 3, 99));
            return $this->attributes[$attribute_name] ?? null;
        }
    }
    /**
     * All
     *
     * @return array
     */
    public function all()
    {
        return $this->attributes;
    }
    /**
     * Create
     *
     * @param string $id
     * @param string $product_name
     * @param string $description
     * @return self
     */
    public static function create(string $id, string $product_name, string $description)
    {
        $object = new self;

        return $object->setId($id)
            ->setProductName($product_name)
            ->setDescription($description);
    }
}
