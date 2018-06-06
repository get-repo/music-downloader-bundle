<?php

namespace GetRepo\MusicDownloaderBundle\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait GuzzleTrait
{
    /**
     * @param string $url
     * @param string $method
     * @return string|false
     */
    protected function request($url, $method = 'GET', array $options = []) {
        try {
            $client = new Client();
            $response = $client->request($method, $url, $options);

            if (200 === $response->getStatusCode()) {
                return (string) $response->getBody();
            }
        } catch (GuzzleException $e) {}

        return false;
    }
}
