<?php

namespace GetRepo\MusicDownloaderBundle\DependencyInjection;

use GetRepo\MusicDownloaderBundle\DependencyInjection\AbstractConfiguration\AbstractTrackConfiguration;

class DeezerTrackConfiguration extends AbstractTrackConfiguration
{
    /**
     * @return string
     */
    protected function getArtistSelector() {
        return 'div[itemprop=byArtist] span[itemprop=name]';
    }

    /**
     * @return string
     */
    protected function getTrackSelector() {
        return 'h1 span[itemprop=name]';
    }
}
