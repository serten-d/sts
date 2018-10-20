<?php

namespace Rest\Api;

use Rest\IApi;
use Communication\ICommunication;

/**
 * 
 */
class Ontariobeerapi implements IApi
{
    private $apiUrl;
    /**
     * @var \Rest\ICommunication
     */
    private $Communication;

    /**
     * 
     * @param ICommunication $Communication communication medium
     */
    public function __construct(ICommunication $Communication)
    {
        $this->Communication = $Communication;
        $this->apiUrl = 'http://ontariobeerapi.ca';
    }

    /**
     * get bewer beers from api
     * 
     * @return array
     */
    public function getBeers()
    {
        $url = $this->apiUrl . '/beers';
        try
        {
            $dataJson = $this->Communication->sendRequest($url, 'GET');
            $data = json_decode($dataJson, TRUE);
            if (!is_array($data))
            {
                $message = __FILE__ . ' line ' . __LINE__ . 'Can\'t decode json from ' . $url;
                Kohana::$log->add(\Log::WARNING, $message);

                $data = [];
            }
        }
        catch (Exception $ex)
        {
            Kohana::$log->add(\Log::ERROR, __FILE__ . ' line ' . __LINE__ . ': Error: ' . $ex->getMessage());
            $data = [];
        }

        return $data;
    }

}
