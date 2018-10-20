<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * Rest API for beer
 *
 * @category Controller
 * @author   atlos
 */
class Controller_Rest_Beers extends Controller_ARest
{
    /**
     * Initializacja
     */
    public function before()
    {
        parent::before();

        $this->restModel = Model_RestAPI::factory('Rest_Beers', $this->_user);
    }
    
    /**
     * Handle GET requests.
     */
    public function action_index()
    {
        try
        {
            /*
             * if id param is set, request is for beer details
             * else request is for beer list
             */
            if($this->request->param('id'))
            {
                $this->rest_output(
                        $this->restModel->getDetails($this->request->param('id'))
                );
            }
            else
            {
                $this->rest_output(
                        $this->restModel->getList(array_merge($this->_params, $this->request->param(), $this->request->query()))
                );
            }
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
