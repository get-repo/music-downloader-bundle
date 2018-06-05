<?php

namespace GetRepo\MusicDownloaderBundle\Fetcher;

use Cocur\Slugify\Slugify;
use GetRepo\MusicDownloaderBundle\Helper\GuzzleTrait;
use Symfony\Component\DomCrawler\Crawler;

class Mp3CCFetcher extends AbstractFetcher
{
    /**
     * @var string
     */
    const URL = 'http://mp3-cc.com/search/f/';

    /**
     * @var string
     */
    const LINK_SELECTOR = 'ul.playlist > li.track a[download]';

    use GuzzleTrait;

    /**
     * {@inheritDoc}
     * @see AbstractFetcher::find()
     */
    public function find($link = null, $artist = null, $album = null, $track = null, $savePath = null) {
        $url = sprintf(
            '%s%s',
            self::URL,
            (new Slugify())->slugify(
                strtolower($artist) . '+' . strtolower($track),
                '+'
            )
        );

        if ($html = $this->request($url)) {
            $crawler = new Crawler($html, $link);

            $tracksEls = $crawler->filter(self::LINK_SELECTOR);
            if ($tracksEls->count() && $link = $tracksEls->first()->attr('href')) {
                return $this->exec(
                    "curl -L '{$link}' -o '{$savePath}'"
                );
            }
        }

        return false;
    }
}