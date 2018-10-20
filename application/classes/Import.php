<?php

use Rest\IApi;
use Rest\Communication\Rest as RestComm;

class Import {
    private $countryCodes;
    
    /**
     * main fonctuin
     *
     * @param \Rest\IApi $RestApi
     */
    protected function _execute(IApi $RestApi = NULL) {
        $this->countryCodes = I18n::load('country_code');
        if(is_null($RestApi))
        {
            $RestApi = new \Rest\Api\Ontariobeerapi(new RestComm);
        }

        $brewers = $this->getDataFromApi($RestApi, 'stores');

        $this->saveBrewers($RestApi, $brewers);

        $existingBeers = $this->getExistingBeers();

        $this->getAndSaveBeers($RestApi, $brewers, $existingBeers);
    }

    /**
     * get beers for all bewers
     * 
     * @param \RestApi $RestApi
     * @param array $bewers bewers teken from api
     * @param array $existingBeers beers existing in our database. expected format:
     * [
     *      bwr_id => [ //bwr_id is id bewer from api, id_bwr is a parent key of bewer table
     *          'id' => id_bwr,
     *          'beers' => [
     *              beer_id => id_beer, //beer_id is id beer from api, id_beer is a parent key of beer table
     *               ..
     *          ]
     *      ],
     *      ..
     * ]
     * @return $this
     */
    private function getAndSaveBeers(\RestApi $RestApi, array $bewers, array &$existingBeers) {
        if (!count($bewers)) {
            return $this;
        }

        foreach ($bewers as $bewer) {
            $brewerBeers = $this->getDataFromApi($RestApi, sprintf('stores/%a/products', $bewer['store_id']));

            $this->saveBewerBeers($bewer['store_id'], $brewerBeers, $existingBeers[$bewer['store_id']] ?? []);
        }
    }

    /**
     * 
     * @return array expected format: 
     * [
     *      bwr_id => id_bwr,
     * ]
     * @throws Exception
     */
    private function getExistingBrewers() {
        $Query = DB::select('ber_id', 'id_bwr')
                ->from(['bewer', 'bwr']);

        try {
            $Result = $Query->execute();
        } catch (Exception $ex) {
            Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__ . ': Database rttot:' . $ex->getMessage());
            throw $ex;
        }

        return [];
    }

    /**
     * save bewers intormation to database
     * 
     * @param aray $brewers data from api
     */
    private function saveBrewers($brewers) {
        $existingBrewers = $this->getExistingBrewers();
        
        foreach ($brewers as $brewerData) {
            if (FALSE === array_key_exists($existingBrewers[$brewerData['store_id']])) {
                $BewerOrm = ORM::factory('Bewer', ['bwr_id' => $brewerData['store_id']]);
                $BewerOrm->bwr_id = intval($brewerData['store_id']);
                $BewerOrm->bwr_name = htmlspecialchars($brewerData['name']);
                $BewerOrm->bwr_address = htmlspecialchars($brewerData['address']);
                $BewerOrm->bwr_city = htmlspecialchars($brewerData['city']);
                $BewerOrm->save();
            }
        }
    }

    /**
     * get beers stored in our database
     * 
     * @return array expected format:
     * [
     *      bwr_id => [ //bwr_id is id bewer from api, id_bwr is a parent key of bewer table
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
    private function getExistingBeers() {
        $Query = DB::select('bwr.id_bwr', 'bwr.bwr_id', 'beer.beer_id', 'beer.id_beer')
                ->from(['bewer', 'bwr'])
                ->from(['beer', 'beer'])
                ->where(['bbwr.id_bwr'], '=', 'beer.id_bwr');

        try {
            $Result = $Query->execute();
        } catch (Exception $ex) {
            Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__ . ': Database rttot:' . $ex->getMessage());
            throw $ex;
        }
        
        $bewers = [];
        foreach ($Result as $aRow) {
            $bewers[$aRow['bwr_id']] = $bewers[$aRow['bwr_id']] ?? [
                'id' => $aRow['id_bwr'],
                'beers' => []
            ];
            $bewers[$aRow['bwr_id']]['beers'][$aRow['beer_id']] = $aRow['id_beer'];
        }

        return $bewers;
    }

    /**
     * save all bewer beers informations to database
     * 
     * @param int $brewerId bewer parent key
     * @param array $beers all beers for one bewer
     * @param array $existingBeers all beers existing in our database
     */
    private function saveBewerBeers($bewerId, &$beers, &$existingBeers) {
        foreach ($beers as $beer) {
            if (isset($existingBeers[$beer['store_id']])) {
                $this->saveBrewerBeer($brewerId, $beer);
            }
        }
    }
    
    /**
     * save one beer intormation to database
     * 
     * @param int $brewerId bewer parent key
     * @param array $beerData data from api
     */
    private function saveBrewerBeer($brewerId, $beerData, $existingBeers) {

        $price = floatval($beerData['price']);
        if (FALSE === array_key_exists($existingBeers[$brewerId][$beerData['store_id']])) {
            $BeerOrm = ORM::factory('Beer', ['beer_id' => $beerData['store_id']]);
            $BeerOrm->id_bwr = intval($brewerId);
            $BeerOrm->beer_id = intval($beerData['beer_id']);
            $BeerOrm->beer_product_id = $beerData['product_id'];
            $BeerOrm->beer_name = htmlspecialchars($beerData['name']);
            $BeerOrm->beer_country_code = $this->countryCodes[$beerData['country']] ?? '';
            $BeerOrm->beer_price = floatval($price);
            $BeerOrm->beer_image = htmlspecialchars($beerData['image_url']);
            $BeerOrm->beer_type = htmlspecialchars($beerData['type']);
            $BeerOrm->beer_on_sale = $beerData['on_sale'] ? '1' : '0';
            $BeerOrm->beer_liter_price = $this->calcPricePerLiter($beerData['beer_id'], $price, $beerData['size']);
            $BeerOrm->save();
        }
    }
    
    /**
     * 
     * @param type $beerId beer id (for logs)
     * @param type $price price per quantity in api
     * @param string $size verbal record of the quantity
     * @return float price fo one liter of beer
     */
    protected function calcPricePerLiter($beerId, $price, $size) {
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
        
        if (FALSE === $count || FALSE === $capacity || FALSE === $unit) {
            \Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__
                    . ': error in parsing beer size:"' . $size . '". as result: count='
                    . var_export($count, TRUE) . ', capacity=' . var_export($capacity, TRUE)
                    . 'unit=' . var_export($unit, TRUE));
            return 0;
        }
        
        $containsInALiter = ($count * $capacity) * $units[$unit];
        
        return $price * $containsInALiter;
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
    protected function parseBeerSize($beerId, $size) {
        $pattern = '/^(?<count>[\d]+)[^\d]+(?<capacity>[\d]+) (?<unit>[a-z]+)$/';
        
        preg_match_all($pattern, $size, $result);
        
        return [
            'capacity' => $result['capacity'] ?? FALSE,
            'unit' => $result['unit'] ?? FALSE,
            'count' => $result['count'] ?? FALSE,
        ];
    }

    /**
     * Get data from Api
     * 
     * @param \RestApi $Api Rest Api to comuniaction
     * @param string $reuestString what to get from api for example: 'stores', 'stores/[STORE ID]/products'
     * @return array recived data
     */
    private function getDataFromApi(\RestApi $Api, $reuestString) {
        $url = 'http://ontariobeerapi.ca/' . $reuestString . '/';
        try {
            $dataJson = $Api->sendApiRequest($url, 'GET');
            $data = json_decode($dataJson, TRUE);
            if (!is_array($data)) {
                Kohana::$log->add(\Log::WARNING, __FILE__ . ' line ' . __LINE__ . ': Can\'t decode json from ' . $url);

                $data = [];
            }
        } catch (Exception $ex) {
            Kohana::$log->add(\Log::ERROR, __FILE__ . ' line ' . __LINE__ . ': Error: ' . $ex->getMessage());
            $data = [];
        }

        return $data;
    }

}