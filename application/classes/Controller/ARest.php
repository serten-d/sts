<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * Rest API for beer
 *
 * @category Controller
 * @author   atlos
 */
abstract class Controller_ARest extends Controller_Rest
{
    /**
     * A Restexample model instance for all the business logic.
     *
     * @var \Model_Rest_Beer
     */
    protected $restModel;

    /**
     * @var int 
     */
    protected $_auth_type = RestUser::AUTH_TYPE_APIKEY;
    protected $_auth_source = RestUser::AUTH_SOURCE_HEADER;

    /**
     * Initializacja
     */
    public function before()
    {
        parent::before();

        $this->_params['appId'] = $this->request->headers('appId');
        
        //type-script has bug and cant send custom headers from angulat. that's why this must be removed
//        $ApiKey = \Kohana::$config->load('rest')->get('key');
//        if($ApiKey !== $this->request->headers('apiKey'))
//        {
//            echo 'Nie masz uprawnień' . "\n" . $ApiKey . ' != ' . $this->request->headers('apiKey');
//            exit;
//        }
        header('Access-Control-Allow-Origin: *');
    }
}
