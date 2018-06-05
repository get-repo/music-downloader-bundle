<?php

namespace GetRepo\MusicDownloaderBundle\Fetcher;

use GetRepo\MusicDownloaderBundle\Helper\ProcessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;

abstract class AbstractFetcher
{
    use ContainerAwareTrait;
    use ProcessTrait;

    protected $output = [
        Process::OUT => null,
        Process::ERR => null,
    ];

    /**
     * @param string $item
     * @param string $savePath
     */
    abstract protected function find($link = null, $artist = null, $album = null, $track = null, $savePath = null);

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->setContainer($container);
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * @param string $filter
     * @return string
     */
    public function getOutput($filter = null) {
        if ($filter && array_key_exists($filter, $this->output)) {
            return $this->output[$filter];
        }

        return trim(implode("\n", $this->output), "\n");
    }
}
