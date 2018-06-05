<?php

namespace GetRepo\MusicDownloaderBundle\DependencyInjection;

use GetRepo\MusicDownloaderBundle\DependencyInjection\AbstractConfiguration\AbstractTrackConfiguration;

class BandcampTrackConfiguration extends AbstractTrackConfiguration
{
    /**
     * @return string
     */
    protected function getArtistSelector() {
        return 'span[itemprop=byArtist]';
    }

    /**
     * @return string
     */
    protected function getTrackSelector() {
        return 'h2.trackTitle';
    }
}
