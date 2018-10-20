<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * Ustawienie szablonu dla skrypto ajaxowych
 */
class Controller_Ajax_Default extends Controller_Default
{

    public function __construct(\Request $request, \Response $response)
    {
        parent::__construct($request, $response);
        
        $this->template = 'ajax';
    }
}
