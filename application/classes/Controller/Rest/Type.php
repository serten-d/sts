<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * Rest API for beer
 *
 * @category Controller
 * @author   atlos
 */
class Controller_Rest_Type extends Controller_ARest
{
    /**
     * Initializacja
     */
    public function before()
    {
        parent::before();

        $this->restModel = Model_RestAPI::factory('Rest_Type', $this->_user);
    }
    
    /**
     * Handle GET requests.
     */
    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
        try
        {
            $this->rest_output(
                    $this->restModel->getList()
            );
        }
        catch (Kohana_HTTP_Exception $khe)
        {
            $this->_error($khe);
            return;
        }
        catch (Kohana_Exception $e)
        {
            $this->_error('An internal error has occurred', 500);
            throw $e;
        }
    }
}
