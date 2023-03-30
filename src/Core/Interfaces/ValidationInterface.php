<?php

namespace Medboubazine\Pay\Core\Interfaces;

interface ValidationInterface
{
    /**
     * Rules
     *
     * @return array
     */
    public function rules(): array;
    /**
     * Errors
     *
     * @return array
     */
    public function errors(): array;
    /**
     * Errors
     *
     * @return array
     */
    public function passed(): bool;
}
