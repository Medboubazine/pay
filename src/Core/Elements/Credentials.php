<?php

namespace Medboubazine\Pay\Core\Elements;

use Medboubazine\Pay\Core\Helpers\Str;

class Credentials
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
}
