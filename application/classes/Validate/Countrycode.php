<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Validate;
use IValidate;

/**
 * varify country code
 *
 * @author dariusz daniec
 */
class Countrycode implements IValidate
{
    public function valid($value)
    {
        $upperValue = strtoupper($value);
        $lang = \I18n::load('country_code');
        $country_code = array_values($lang['country codes']);
        if(in_array($upperValue, array_values($country_code)))
        {
            return $upperValue;
        }
        return NULL;
    }
}
