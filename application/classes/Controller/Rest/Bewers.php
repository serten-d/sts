<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * Rest API for beer
 *
 * @category Controller
 * @author   atlos
 */
class Controller_Rest_Bewers extends Controller_ARest
{

    /**
     * Initializacja
     */
    public function before()
    {
        parent::before();

        $this->_rest = Model_RestAPI::factory('Rest_Bewers', $this->_user);
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
                    $this->_rest->get(array_merge($this->_params, $this->request->param(), $this->request->query()))
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
