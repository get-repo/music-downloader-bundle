<?php

namespace GetRepo\MusicDownloaderBundle\Helper;

use Symfony\Component\Process\Process;

trait ProcessTrait
{
    /**
     * @param string $command
     * @param boolean $returnProcess
     * @param boolean $live
     * @return boolean|Process
     */
    protected function exec($command, $returnProcess = false, $live = false) {
        $process = new Process($command);
        $process->setTimeout(null);

        if ($live) {
            $process->start();
            $process->wait(function ($type, $buffer) {
                echo fwrite(
                    (Process::ERR === $type ? STDERR : STDOUT),
                    $buffer
                );
            });
        } else {
            $process->run();
        }

        return ($returnProcess ? $process : $process->isSuccessful());
    }
}
