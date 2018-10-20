<?php
namespace Validate;

use IValidate;
/**
 * 
 *
 * @author dariu
 */
class FloatValue implements IValidate
{
    public function valid($value)
    {
        return floatval($value);
    }
}
