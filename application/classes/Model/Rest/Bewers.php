<?php

defined('SYSPATH') or die('No direct access allowed.');
/**
 * Rest API dla wewnetrznych importow ogloszen
 *
 * @author   atlos
 */
class Model_Rest_Bewers extends Model_RestAPI
{
    /**
     * get bewers list
     *
     * @param array $params expected params limit, offset
     * @return array format: [
     *      'beers' => [,
     *          id_bwr => [
     *              'id_bwr' => bewer parent key in local database,
     *              'bwr_id' => bewer id from api,
     *              'bwr_name', bewer name,
     *              'beer_count' => nrres count for bewer ,
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
    public function get($params)
    {
        $verified = $this->validate(array_filter($params, 'strlen'), [
            'limit' => new \Validate\Num(),
            'offset' => new \Validate\Num()
        ]);

        $verified['limit'] = is_numeric($verified['limit']) ? $verified['limit'] : 10;
        $verified['offset'] = is_numeric($verified['offset']) ? $verified['offset'] : 0;
        
        $DBBewers = $this->buildQuery($verified);
        
        try
        {
            $ResultBeers = $DBBewers->execute();
            $bewers = $ResultBeers->as_array();
        }
        catch (\Exception $Exc)
        {
            $bewers = [];
        }

        $DBBewersCount1 = \DB::select(
                        DB::expr('COUNT(bwr.id_bwr) AS liczba')
                )
                ->from(['bewer', 'bwr']);

        try
        {
            $iBewersCount = $DBBewersCount1->execute()->get('liczba');
        }
        catch (\Exception $Exc)
        {
            $iBewersCount = 0;
        }
        
        return [
            'bewers' => $bewers,
            'count_all' => $iBewersCount,
            'limit' => $verified['limit'],
            'offset' => $verified['offset'],
        ];
    }
    
    /**
     * 
     * @param array $verified
     * @return \Database_Query_Builder_Select
     */
    protected function buildQuery($verified)
    {
        $DBBewers = \DB::select(
                    'bwr.id_bwr',
                    'bwr_name',
                    \DB::expr('count(id_beer) as beer_count')
                )
                ->from(['bewer', 'bwr'])
                ->join(['beer', 'beer'], 'left')
                ->on('beer.id_bwr', '=', 'bwr.id_bwr')
                ->on('beer_removed', '=', \DB::expr(0))
                ->where('bwr.bwr_removed', '=', 0)
                ->group_by('bwr.id_bwr')
                ->order_by('beer_count', 'DESC');
        
        if($verified['limit'])
        {
            $DBBewers->limit($verified['limit'])
                ->offset($verified['offset']);
        }
        
        return $DBBewers;
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
            if(FALSE === isset($params[$paramName]))
            {
                $validated[$paramName] = NULL;
                continue;
            }
            
            $validated[$paramName] = $ValidateClass->valid($params[$paramName]);
        }
        
        return $validated;
    }
}
