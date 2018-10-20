<?php
namespace Validate;

use IValidate;
/**
 * 
 *
 * @author dariu
 */
class Text implements IValidate
{
    public function valid($value)
    {
        return htmlspecialchars($value);
    }
}
