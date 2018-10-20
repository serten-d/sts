<?php

use Where\Condidtions;

defined('SYSPATH') or die('No direct access allowed.');
/**
 * Rest API model prepares data list for rest
 *
 * @author   dariusz daniec
 */
class Model_Rest_Type extends Model_RestAPI
{
    /**
     * get beer list
     *
     * @return array format: [
     *      beer_type => beer_type,
     *      ..
     * ]
     *
     * @throws type
     */
    public function getList()
    {
        $DBBeers = \DB::select(\DB::expr('distinct beer.beer_type'))
                ->from(['beer', 'beer'])
                ->where('beer.beer_removed', '=', 0);
        
        try
        {
            $types = $DBBeers->execute()->as_array('beer_type', 'beer_type');
        }
        catch (\Exception $Exc)
        {
            $types = [];
        }
        
        return $types;
    }
}
