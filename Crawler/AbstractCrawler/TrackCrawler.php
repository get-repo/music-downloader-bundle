<?php

namespace GetRepo\MusicDownloaderBundle\Crawler\AbstractCrawler;

use Symfony\Component\DomCrawler\Crawler;

class TrackCrawler extends AbstractCrawler
{
    /**
     * {@inheritDoc}
     * @see AbstractCrawler::doParse()
     */
    protected function doParse(Crawler $crawler) {
        // find artist
        $this->output->write("artist: ");
        $artistEl = $crawler->filter($this->config['selectors']['artist']);
        if ($artistEl->count() !== 1) {
            return $this->output->writeln("<error>[FAILED]</error>");
        }
        $artist = $this->clean($artistEl->text());
        $this->output->writeln("<info>{$artist}</info>");

        // find track
        $this->output->write("track: ");
        $trackEl = $crawler->filter($this->config['selectors']['track']);
        if ($trackEl->count() !== 1) {
            return $this->output->writeln("<error>[FAILED]</error>");
        }
        $track = $this->clean($trackEl->text());
        $this->output->writeln("<info>{$track}</info>");

        $this->output->writeln("Fetching");
        foreach ($this->fetchers as $fetcherName => $fetcher) {
            $this->output->write(" > {$fetcherName} ... ");
            $success = $fetcher->find(
                $crawler->getUri(),
                $artist,
                null,
                $track,
                $this->createSingleTrackPath($artist, $track)
            );

            if ($success) {
                $this->output->writeln(null);
                break;
            }
            $this->output->writeln("[FAILED]");
        }

        if ($success) {
            $this->output->writeln("<info>[OK]</info>");
        } else {
            $this->output->writeln("<error>[FAILED]</error>");
            $this->output->writeln($fetcher->getOutput());

            return false;
        }
    }
}
