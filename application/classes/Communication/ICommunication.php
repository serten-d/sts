<?php

namespace Communication;

/**
 *
 * @author dariu
 */
interface ICommunication
{
    /**
     * @param string $url
     * @param string $method GET, POST, PUT
     * @param array|string $data array or JSON
     * @return bool|string response JSON or FALSE if communication error
     */
    public function sendRequest($url, $method, $data = array());
}
