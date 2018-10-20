<?php

use Where\Condidtions;

defined('SYSPATH') or die('No direct access allowed.');
/**
 * Rest API model prepares data list for rest
 *
 * @author   dariusz daniec
 */
class Model_Rest_Country extends Model_RestAPI
{
    /**
     * get beer list
     *
     * @return array format: [
     *      ccountry code => country name,
     *      ..
     * ]
     *
     * @throws type
     */
    public function getList()
    {
        $country_code = I18n::load('country_code');
        
        return array_flip($country_code['country']);
    }
}
