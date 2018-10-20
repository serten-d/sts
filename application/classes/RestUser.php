<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * 
 */
class RestUser extends Kohana_RestUser
{

    /**
     * A mock loading of a user object.
     */
    protected function _find()
    {
        $sApiKey = Kohana::$config->load('rest')->get('key');
        $aRestMcm = Kohana::$config->load('rest')->get('mcm');

        if ($this->_api_key == $sApiKey)
        {
            $this->_id = 1;
            $this->_secret_key = 'ok';
            $this->_roles = array('developer');
        }

        if ($this->_api_key == $aRestMcm['apiKey'])
        {
            $this->_id = 2;
            $this->_secret_key = 'ok';
            $this->_roles = array('developer');
        }
    }
}
