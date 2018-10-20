<?php

use Where\Condidtions;

defined('SYSPATH') or die('No direct access allowed.');
/**
 * Rest API model prepares data list for rest
 *
 * @author   dariusz daniec
 */
class Model_Rest_Beers extends Model_RestAPI
{
    private $paramsToDbname = [
        'id' => 'beer.beer_id',
        'bwr' => 'beer.id_bwr',
        'name__li' => 'beer.beer_name',
        'price__from' => 'beer.beer_price',
        'price__to' => 'beer.beer_price',
        'country_code' => 'beer.beer_country_code',
        'type' => 'beer.beer_type',
    ];
    
    /**
     * get beer list
     *
     * @param array $params expected params id, name__li, price__from, price__to, country_code, type, limit, offset
     * @return array format: [
     *      'beers' => [,
     *          id_beer => [
     *              'id_beer' => beed parent id from local database,
     *              'beer_id' => beer id from api,
     *              'id_bwr' => bewer parent id from local database,
     *              'bwr_id' => bewer id from api,
     *              'beer_name' => beer name,
     *              'bwr_name' => bewer name,
     *              'beer_type' => beer type,
     *              'beer_country' => beer country name,
     *              'beer_country_code' => beer country code,
     *              'beer_liter_price' => price for liter of beer,
     *          ],
     *          ..
     *      ],
     *      'count_all' => count of all bewer in list,
     *      'limit' => actual limit of oferts in page
     *      'offset' => page number,
     * ]
     *
     * @throws type
     */
    public function getList($params)
    {
        $verified = $this->verifyFilters($params);
        
        $DBBeers = $this->buildQueryList($verified);
        
        $beersSql = $DBBeers->compile();
        
        try
        {
            $ResultBeers = $DBBeers->execute();
        }
        catch (\Exception $Exc)
        {
            var_dump('$Exc', $Exc->getMessage());
        }

        $beers = $ResultBeers->as_array();

        $DBCountBeers = $this->buildQueryCount($DBBeers);
        
        try
        {
            $iBeersCount = $DBCountBeers->execute()->get('liczba');
        }
        catch (\Exception $Exc)
        {
            
        }
        
        return [
            'beers' => $beers,
            'count_all' => $iBeersCount,
            'limit' => $verified['limit'],
            'offset' => $verified['offset'],
        ];
    }
    
    /**
     * verification of sent filters
     * 
     * @param array $params filters
     * @return array verified filters
     */
    protected function verifyFilters($params)
    {
        $verified = $this->validate(array_filter($params, 'strlen'), [
            'id' => new \Validate\Num(),
            'bwr' => new \Validate\Num(),
            'name__li' => new \Validate\Text(),
            'price__from' => new \Validate\FloatValue(),
            'price__to' => new \Validate\FloatValue(),
            'country_code' => new \Validate\Countrycode(),
            'type' => new \Validate\Text(),
            'limit' => new \Validate\Num(),
            'offset' => new \Validate\Num()
        ]);
        
        $verified['limit'] = is_numeric($verified['limit']) ? $verified['limit'] : 10;
        $verified['offset'] = is_numeric($verified['offset']) ? $verified['offset'] : 0;
        
        return $verified;
    }
    
    /**
     * preparation of a db query to select list of beers
     * 
     * @param array $verified
     * @return \Database_Query_Builder_Select
     */
    protected function buildQueryList($verified)
    {
        $WhereCondition = new Condidtions();
        $DBBeers = \DB::select_array([
                    'beer.id_beer',
                    'beer.beer_id',
                    'bwr.id_bwr',
                    'beer.beer_name',
                    'bwr.bwr_name',
                    'beer.beer_type',
                    'beer.beer_country',
                    'beer.beer_country_code',
                    'beer.beer_liter_price',
                ])
                ->from(['beer', 'beer'])
                ->from(['bewer', 'bwr'])
                ->where('bwr.id_bwr', '=', \DB::expr('beer.id_bwr'))
                ->where('beer.beer_removed', '=', 0);
        
        if($verified['limit'])
        {
            $DBBeers->limit($verified['limit'])
                ->offset($verified['offset']);
        }
        
        $conditions = $WhereCondition->prepare(array_filter($verified), $this->paramsToDbname, ['limit', 'offset']);
        if(is_array($conditions))
        {
            foreach ($conditions as $whereParts)
            {
                $DBBeers->where($whereParts['column'], $whereParts['operator'], $whereParts['value']);
            }
        }
        
        return $DBBeers;
    }
    
    /**
     * preparation of a db query to take count all results of list beers
     * 
     * @param Database_Query_Builder_Select $DBQuery query that retrieves the list of beers with necessary filters
     * @return \Database_Query_Builder_Select
     */
    protected function buildQueryCount($DBQuery)
    {
        $DBQuery->reset_select_columns()
                    ->select(DB::expr('COUNT(id_beer) AS liczba'))
                    ->limit(NULL)
                    ->offset(NULL);
        
        return $DBQuery;
    }
    
    /**
     * get detail information about one beer
     *
     * @param int $beerId beer id
     * @return array
     *
     * @throws type
     */
    public function getDetails($beerId)
    {
        $IntValidator = new \Validate\Num();
        $valdateBeerId = $IntValidator->valid($beerId);
        if(!$valdateBeerId)
        {
            throw new Exception('Wrong beer id');
        }
        
        $DBBeers = \DB::select('*')
                ->from(['beer', 'beer'])
                ->where('beer_removed', '=', 0)
                ->where('id_beer', '=', $valdateBeerId)
                ->limit(1);
        
        try
        {
            $ResultBeers = $DBBeers->execute();
        }
        catch (\Exception $Exc)
        {
            
        }
        return $ResultBeers->current();
    }

    /**
     * get all expected params from $params.
     * If some param not exist, his value in result array will be NULL.
     *
     * @param string $sMethod
     * @return array validated params. Expected format: 
     * [
     *      param_name => param_value,
     *      ...
     * ]
     * if param not exist in $param, param_value will be NULL.
     */
    protected function validate($params, $expectedParams)
    {
        $validated = [];
        /* @var $ValidateClass \IValidate */
        foreach ($expectedParams as $paramName => $ValidateClass)
        {
            if(FALSE === array_key_exists($paramName, $params))
            {
                $validated[$paramName] = NULL;
                continue;
            }
            
            $validated[$paramName] = $ValidateClass->valid($params[$paramName]);
        }
        
        return $validated;
    }
}
