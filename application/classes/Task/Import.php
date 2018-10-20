<?php

use Rest\Api\Ontariobeerapi;
use Communication\Rest;
use Rest\IApi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Task_Import extends Minion_Task
{

    /**
     * Parametry wywolania
     * @var array
     */
    protected $_options = [
    ];
    private $countryCodes;
    private $dateNow;
    
    private $iBeersActualized = 0;
    private $beersAdded = 0;
    private $bewersActualized = 0;
    private $bewersAdded = 0;
    private $beersRemoved = 0;
    private $bewersRemoved = 0;

    /**
     * main fonctuin
     *
     * @param array $params
     */
    protected function _execute(array $params)
    {
        $this->dateNow = date('Y-m-d H:i:s');
        $this->countryCodes = I18n::load('country_code');
        $RestApi = new Ontariobeerapi(new Rest());

        $existingBeers = $this->getExistingBewersAndBeers();
        $beers = $RestApi->getBeers();
        
        $this->saveBewerAndBeers($beers, $existingBeers);
        
        echo 'End' . "\n" 
                 . "\n" . 'beer added:' . $this->beersAdded 
                 . "\n" . 'bewers added: ' . $this->bewersAdded 
                 . "\n" . 'beers removed: ' . $this->beersRemoved 
                 . "\n" . 'bewers removed: :' . $this->bewersRemoved
                 . "\n" ;
    }

    /**
     * save bewers intormation to database
     * 
     * @param aray $brewerName data from api
     */
    private function saveBrewer($brewerName)
    {
        $BewerOrm = ORM::factory('Bewer', ['bwr_name' => $brewerName]);
        $BewerOrm->bwr_name = htmlspecialchars($brewerName);
        $BewerOrm->bwr_add_date = $this->dateNow;
        $BewerOrm->bwr_removed = '0';
        try
        {
            echo "\n " . __LINE__ . '$brewerName:' . $brewerName;
            $BewerOrm->save();
            echo "\n " . __LINE__;
            
        }
        catch (\Exception $ex)
        {
            var_dump('$ex', $ex->getMessage());
            exit;
            
        }
        
        return $BewerOrm->pk();
    }

    /**
     * get bewers and his beers stored in local database
     * 
     * @return array expected format:
     * [
     *      bwr_name => [ //bwr_name is a name of berew fro api
     *          'id' => id_bwr,
     *          'beers' => [
     *              beer_id => id_beer, //beer_id is id beer from api, id_beer is a parent key of beer table
     *               ..
     *          ]
     *      ],
     *      ..
     * ]
     *
     * @throws Exception on database exception
     */
    private function getExistingBewersAndBeers()
    {
        $Query = DB::select('bwr.id_bwr', 'bwr.bwr_name', 'beer.beer_id', 'beer.id_beer')
                ->from(['bewer', 'bwr'])
                ->join(['beer', 'beer'], 'LEFT')
                ->on('bwr.id_bwr', '=', 'beer.id_bwr');

        try
        {
            $Result = $Query->execute();
        }
        catch (\Exception $ex)
        {
            Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__ . ': Database rttot:' . $ex->getMessage());
            throw $ex;
        }

        $bewers = [];
        foreach ($Result as $aRow)
        {
            $sname = htmlspecialchars_decode($aRow['bwr_name']);
            if (!array_key_exists($sname, $bewers))
            {
                $bewers[$sname] = [
                    'id' => $aRow['id_bwr'],
                    'beers' => []
                ];
            }

            //id_beer can be NULL id bewer has no beers in loal database
            if ($aRow['id_beer'])
            {
                $bewers[$sname]['beers'][$aRow['beer_id']] = $aRow['id_beer'];
            }
        }

        return $bewers;
    }

    /**
     * save all bewer beers informations to database
     * 
     * @param int $brewerId bewer parent key
     * @param array $beers all beers for one bewer
     * @param array $existingBewersAndBeers beers existing in our database. expected format:
     * [
     *      bwr_name => [ //bwr_name is a name of berew fro api
     *          'id' => id_bwr,
     *          'beers' => [
     *              beer_id => id_beer, //beer_id is id beer from api, id_beer is a parent key of beer table
     *               ..
     *               ..
     *          ]
     *      ],
     *      ..
     * ]
     */
    private function saveBewerAndBeers(array &$beers, array &$existingBewersAndBeers)
    {
        $allBewers = [];
        foreach ($existingBewersAndBeers as $bewerName => $bewer)
        {
            if(!isset($bewer['id']))
            {
                var_export($bewer);
            }
            $allBewers[$bewerName] = $bewer['id'];
        }
        
        foreach ($beers as $beer)
        {
            $Bewer = $existingBewersAndBeers[$beer['brewer']] ?? NULL;
            
            if (is_null($Bewer))
            {
                $bewerId = $this->saveBrewer($beer['brewer']);
                $existingBewersAndBeers[$beer['brewer']] = [
                    'id' => $bewerId,
                    'beers' => []
                ];
                $this->bewersAdded++;
            }
            else
            {
                $bewerId = $existingBewersAndBeers[$beer['brewer']]['id'];
                //need to find bewers removed from list (have no longr eny beer)
                unset($allBewers[$beer['brewer']]);
                $this->bewersActualized++;
            }
            
            $beerId = $existingBewersAndBeers[$beer['brewer']]['beers'][$beer['beer_id']] ?? NULL;
            
            if(isset($existingBewersAndBeers[$beer['brewer']]['beers'][$beer['beer_id']]))
            {
                $this->bewersActualized++;
            }
            else
            {
                $this->beersAdded++;
            }
            
            $beerId = $this->saveBrewerBeer($bewerId, $beer, $beerId);
            
            //remove existing beer to fin beers anb bewers no existing ano more
            unset($existingBewersAndBeers[$beer['brewer']]['beers'][$beer['beer_id']]);
        }
        
        foreach ($existingBewersAndBeers as $bewerName =>  $bewer)
        {
            if(count($bewer['beers'])){
                $this->beersRemoved += count($bewer['beers']);
                $this->removeBeers(array_keys($bewer['beers']));
            }
            
        }
        
        if(count($allBewers))
        {
            $this->bewersRemoved = count($allBewers);
            $this->removeBewers(array_keys($allBewers));
        }
    }

    /**
     * remove bewers with no one beer
     * 
     * @param string[] $allBewers list of bwr_name
     */
    private function removeBewers($allBewers)
    {
        if(!count($allBewers))
        {
            return $this;
        }
        
        $DBUpdate = \DB::update('bewer')
                ->set(['bwr_removed' => '1'])
                ->where('bwr_name', 'IN', $allBewers);
        
        try
        {
            $DBUpdate->execute();
        }
        catch (\Exception $ex)
        {
            \Kohana::$log->add(\Log::ERROR, __FILE__ . ' line ' . __LINE__ . ': DB Erron: ' . $ex->getMessage());
        }
        
        return $this;
    }
    
    /**
     * remove beers no longer exist in api
     * 
     * @param int[] $abeerIds list of beer_id
     */
    private function removeBeers($abeerIds)
    {
        if(!count($abeerIds))
        {
            return $this;
        }
        
        $DBUpdate = \DB::update('beer')
                ->set(['beer_removed' => '1'])
                ->where('beer_id', 'IN', $abeerIds);
        
        try
        {
            $DBUpdate->execute();
        }
        catch (\Exception $ex)
        {
            \Kohana::$log->add(\Log::ERROR, __FILE__ . ' line ' . __LINE__ . ': DB Erron: ' . $ex->getMessage());
        }
        
        return $this;
    }

    /**
     * add or update beer intormation to database
     * 
     * @param int $brewerId bewer parent key
     * @param array $beerData data from api
     * @param int $beerId breer id from local database
     */
    private function saveBrewerBeer($brewerId, $beerData, $beerId)
    {
        $price = floatval($beerData['price']);
        if(!is_null($beerId))
        {
            $BeerOrm = ORM::factory('Beer', $beerId);
        }
        
        if(!isset($BeerOrm) || false === $BeerOrm->loaded())
        {
            $BeerOrm = ORM::factory('Beer', ['id_bwr' => $brewerId, 'beer_id' => $beerData['beer_id']]);
        }
        
        $BeerOrm->id_bwr = intval($brewerId);
        $BeerOrm->beer_id = intval($beerData['beer_id']);
        $BeerOrm->beer_product_id = $beerData['product_id'];
        $BeerOrm->beer_name = htmlspecialchars($beerData['name']);
        $BeerOrm->beer_country_code = $this->countryCodes[$beerData['country']] ?? '';
        $BeerOrm->beer_price = $price;
        $BeerOrm->beer_price_per_size = floatval($beerData['price']);
        $BeerOrm->beer_image = htmlspecialchars($beerData['image_url']);
        $BeerOrm->beer_type = htmlspecialchars($beerData['type']);
        $BeerOrm->beer_size = htmlspecialchars($beerData['size']);
        $BeerOrm->beer_country = htmlspecialchars($beerData['country']);
        $BeerOrm->beer_removed = '0';
        $BeerOrm->beer_on_sale = true === $beerData['on_sale'] ? '1' : '0';
        $BeerOrm->beer_liter_price = $this->calcPricePerLiter($beerData['beer_id'], $price, $beerData['size']);
        $BeerOrm->beer_add_date = $this->dateNow;
        $BeerOrm->save();
        
        return $BeerOrm->pk();
    }

    /**
     * 
     * @param type $beerId beer id (for logs)
     * @param type $price price per quantity in api
     * @param string $size verbal record of the quantity
     * @return float price fo one liter of beer
     */
    protected function calcPricePerLiter($beerId, $price, $size)
    {
        $units = [
            'l' => 1,
            'dl' => 0.1,
            'cl' => 0.01,
            'ml' => 0.001,
        ];
        
        $parsed = $this->parseBeerSize($beerId, $size);

        $capacity = $parsed['capacity'];
        $unit = $units[$parsed['unit']] ?? FALSE;
        $count = $parsed['count'];

        if (FALSE === $count || FALSE === $capacity || FALSE === $unit)
        {
            \Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__
                    . ': error in parsing beer size:"' . $size . '". as result: count='
                    . var_export($count, TRUE) . ', capacity=' . var_export($capacity, TRUE)
                    . 'unit=' . var_export($unit, TRUE));
            return 0;
        }

        $containsInALiter = (floatval($count) * floatval($capacity)) * floatval($unit);
        
        return round($price * $containsInALiter, 2);
    }

    /**
     * parse verbal string of quantity and calculate how many times it will fill in a liter
     * 
     * @param type $beerId beer id (for logs)
     * @param string $size verbal record of the quantity
     * @return array [
     *      'capacity' => FALSE || value of capacity in size,
     *      'unit' => FALSE || size of one bottle (for example "ml")
     *      'count' => FALSE || count of bottle,
     * ]
     * where FALSE is return if property not exist in size
     */
    protected function parseBeerSize($beerId, $size)
    {
        $pattern = '/(?<count>[0-9]+)[^\d]*(?<capacity>[0-9]+)[^a-zA-Z]*(?<unit>[a-zA-Z]*)/';

        $s = preg_match($pattern, $size);
        
        $result = [];
        preg_match_all($pattern, $size, $result);
        return [
            'capacity' => $result['capacity'][0] ?? FALSE,
            'unit' => $result['unit'][0] ?? FALSE,
            'count' => $result['count'][0] ?? FALSE,
        ];
    }

    /**
     * Get data from Api
     * 
     * @param \Rest\IApi $Api Rest Api to comuniaction
     * @param string $reuestString what to get from api for example: 'stores', 'stores/[STORE ID]/products'
     * @return array recived data
     */
    private function getDataFromApi(IApi $Api)
    {
        try
        {
            $dataJson = $Api->getBeers();
            $data = json_decode($dataJson, TRUE);
            if (!is_array($data))
            {
                Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__ . ': Can\'t decode json from ' . $url);

                $data = [];
            }
        }
        catch (\Exception $ex)
        {
            Kohana::$log->add(\Log::ERROR, __FILE__ . ' line ' . __LINE__ . ': Error: ' . $ex->getMessage());
            $data = [];
        }

        return $data;
    }

}
