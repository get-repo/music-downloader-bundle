<?php

namespace GetRepo\MusicDownloaderBundle\Crawler;

use Symfony\Component\DomCrawler\Crawler;
use GetRepo\MusicDownloaderBundle\Crawler\AbstractCrawler\AbstractCrawler;

class BandcampAlbumCrawler extends AbstractCrawler
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

        // find album
        $this->output->write("album: ");
        $albumEl = $crawler->filter($this->config['selectors']['album']);
        if ($albumEl->count() !== 1) {
            return $this->output->writeln("<error>[FAILED]</error>");
        }
        $album = $this->clean($albumEl->text());
        $this->output->writeln("<info>{$album}</info>");

        // find tracks
        $this->output->write("tracks: ");
        $tracksEls = $crawler->filter($this->config['selectors']['tracks']);
        if (!$nbTracks = $tracksEls->count()) {
            return $this->output->writeln("<error>[FAILED]</error>");
        }
        $tracks = [];
        $tracksEls->each(
            function (Crawler $node) use (&$tracks) {
                $tracks[$this->clean($node->text())] = $node->attr('href');
            }
        );

        $this->output->writeln("<info>{$nbTracks}</info>");

        $this->output->write("directory: ");
        if (!$path = $this->createAlbumDirectory($artist, $album)) {
            return $this->output->writeln("<error>[FAILED]</error>");
        }
        $this->output->writeln("<info>" . basename($path) . "</info>");

        // find cover
        $this->output->write("cover: ");
        $coverEl = $crawler->filter($this->config['selectors']['cover']);
        $cover = false;
        if ($coverEl->count() === 1) {
            $cover = trim($coverEl->attr('src'));
        }
        $this->output->writeln($cover ? "<info>OK</info>" : "FAILED");

        $this->output->writeln("");

        if ($cover) {
            $this->output->write("Get cover... ");
            if (!$this->exec("curl -L '{$cover}' -o '{$path}/cover.jpg'")) {
                $this->output->writeln("<error>[FAILED]</error>");
            } else {
                $this->output->writeln("<info>[OK]</info>");
            }
        }

        // Download tracks
        $i = 1;
        $parsedUrl = parse_url($crawler->getUri());
        $this->output->writeln("Get tracks:");
        foreach ($tracks as $name => $trackLink) {
            $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
            $nbTrack = sprintf('%02d', $i);
            $trackLink = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$trackLink}";

            $this->output->writeln(" > <comment>{$nbTrack} {$name}</comment> ");

            $success = false;
            foreach ($this->fetchers as $fetcherName => $fetcher) {
                $this->output->write("    {$fetcherName} ... ");
                $success = $fetcher->find(
                    $trackLink,
                    $artist,
                    $album,
                    $name,
                    "{$path}/{$nbTrack} {$name}.mp3"
                );

                if ($success) {
                    $this->output->writeln("<info>[OK]</info>");

                    break;
                }

                $this->output->writeln("[FAILED]");
            }

            if (!$success) {
                $this->output->writeln("<error>[FAILED]</error>");
                return false;
            }
            $i++;
        }

        $this->output->writeln("\n<info>Done!</info>");
    }
}
