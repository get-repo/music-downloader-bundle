<?php

namespace GetRepo\MusicDownloaderBundle\Fetcher;

use GetRepo\MusicDownloaderBundle\Helper\GuzzleTrait;

class DatMusicFetcher extends AbstractFetcher
{
    /**
     * @var string
     */
    const URL = 'https://api-2.datmusic.xyz/search?q=';

    /**
     * @var string
     */
    const REFERER = 'https://datmusic.xyz/?q=';

    /**
     * @var array
     */
    const BYTES = [320, 192, 128];

    use GuzzleTrait;

    /**
     * {@inheritDoc}
     * @see AbstractFetcher::find()
     */
    public function find($link = null, $artist = null, $album = null, $track = null, $savePath = null) {
        $url = sprintf(
            '%s%s',
            self::URL,
            ($query = urlencode(strtolower($artist) . ' ' . strtolower($track)))
        );

        $headers = [
            'origin' => parse_url(self::REFERER)['host'],
            'referer' => self::REFERER . $query,
            'authority' => parse_url(self::URL)['host'],
        ];

        if ($json = $this->request($url, 'GET', ['headers' => $headers])) {

            $json = @json_decode($json, true);
            if ($json && isset($json['status']) && $json['status'] === 'ok' && $json['data']) {

                $curlHeaders = '';
                foreach ($headers as $k => $v) {
                    $curlHeaders .= sprintf(
                        '-H "%s: %s" ',
                        $k,
                        $v
                    );
                }

                foreach ($json['data'] as $data) {
                    foreach (self::BYTES as $bytes) {
                        $url = $data['download'] . '/' . $bytes;
                        if ($this->exec("curl {$curlHeaders} -L '{$url}' -o '{$savePath}'")) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}