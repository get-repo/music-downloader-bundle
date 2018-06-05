<?php

namespace GetRepo\MusicDownloaderBundle\Fetcher;

use Exception;
use Symfony\Component\Process\Process;

class YoutubeDLFetcher extends AbstractFetcher
{
    private $bin;

    public function init() {
        $bin = trim(@exec('which youtube-dl'));
        if (!$bin) {
            $bin = $this->container->getParameter('kernel.root_dir') . '/../vendor/bin/youtube-dl';
            if (!file_exists($bin)) {
                if ($this->exec("curl -L https://yt-dl.org/downloads/latest/youtube-dl -o {$bin}")) {
                    $this->exec("chmod a+rx {$bin}");
                }
            }
        }

        if (!$bin || !file_exists($bin) || !is_readable($bin)) {
            throw new Exception("Failed to find youtube-dl");
        }

        $this->bin = $bin;
    }

    /**
     * {@inheritDoc}
     * @see AbstractFetcher::find()
     */
    public function find($link = null, $artist = null, $album = null, $track = null, $savePath = null) {
        if (!$link) {
            return false;
        }

        $cmd = $this->bin;

        if ($savePath) {
            $cmd .= sprintf(
                ' -o"%s"',
                str_replace('"', '\"', $savePath)
            );
        }

        $cmd .= " --prefer-ffmpeg '{$link}'";
        $process = new Process($cmd);
        $process->start();

        $success = true;
        $process->wait(function ($type, $buffer) use (&$success) {
            $this->output[$type] .= $buffer;
            if (Process::ERR === $type) {
                $success = false;
            }
        });

        return ($success && $process->isSuccessful());
    }
}