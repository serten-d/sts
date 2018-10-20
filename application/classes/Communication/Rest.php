<?php
namespace Communication;

use Communication\ICommunication;

class Rest implements ICommunication
{

    /**
     * @param string $restApiUrl
     * @param string $method GET, POST, PUT
     * @param array|string $data array or JSON
     * @return bool|string response JSON or FALSE if communication error
     */
    public function sendRequest($restApiUrl, $method, $data = array()) {
        $headers = array(
            "Content-Type: application/vnd.allegro.public.v1+json",
            "Accept: application/vnd.allegro.public.v1+json"
        );

        if(is_array($data))
        {
            $dataJson = json_encode($data);
        }

        return $this->sendHttpRequest($restApiUrl, $method, $headers, $dataJson);
    }

    /**
     * @param string $restApiUrl
     * @param string $method GET, POST, PUT
     * @param array $headers
     * @param string $dataJson
     * @return bool|string response JSON or FALSE if communication error
     */
    private function sendHttpRequest($restApiUrl, $method, array $headers = array(), $dataJson = '') {
        $options = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $dataJson,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($options);

        return file_get_contents($restApiUrl, false, $context);
    }
}