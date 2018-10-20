<?php

namespace Validate;

use IValidate;
/**
 * validate int
 *
 * @author dariu
 */
class Num implements IValidate
{
    public function valid($value)
    {
        return intval($value);
    }
}
