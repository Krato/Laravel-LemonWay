<?php

namespace Infinety\LemonWay\Validations;

class BicSwiftValidator
{
    /**
     * Validate whether the given value is a valid BIC/SWIFT.
     *
     * @param string $attribute
     * @param string $value
     *
     * @return bool
     */
    public function validate($attribute, $value)
    {
        return $this->isValid($value);
    }

    /**
     * @param $value
     */
    private function isValid($value)
    {
        if (preg_match('/^[0-9a-z]{4}[a-z]{2}[0-9a-z]{2}([0-9a-z]{3})?\z/i', $value)) {
            return true;
        } else {
            return false;
        }
    }
}
