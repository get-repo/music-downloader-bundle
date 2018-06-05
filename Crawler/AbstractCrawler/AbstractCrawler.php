<?php

namespace GetRepo\MusicDownloaderBundle\Crawler\AbstractCrawler;

use GetRepo\MusicDownloaderBundle\Helper\ProcessTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractCrawler
{
    use ContainerAwareTrait;
    use ProcessTrait;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $fetchers;

    /**
     * @param Crawler $crawler
     */
    abstract protected function doParse(Crawler $crawler);

    /**
     * @param array $config
     * @param ContainerInterface $container
     * @param OutputInterface $output
     */
    public function __construct(array $config, ContainerInterface $container, OutputInterface $output) {
        $this->setContainer($container);
        $this->output = $output;
        $this->config = $config['config'];

        // check fetchers exists
        foreach(array_keys($config['fetchers']) as $name) {
            if (!$container->has($name)) {
                throw new \LogicException("Fetcher service '{$name}' not found");
            }
            $this->fetchers[$name] = $container->get($name);
        }
    }

    /**
     * @param  string $url
     * @return bool
     */
    public function parse($url) {
        $this->output->writeln("saving in <comment>{$this->getSavePath()}</comment>\n");
        $this->output->write('crawling... ');
        if (!$crawler = $this->getHtmlCrawler($url)) {
            $this->output->writeln("<error>[FAILED]</error>");

            return false;
        }

        $this->output->writeln('<info>[OK]</info>');

        $this->doParse($crawler);
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getHtmlCrawler($url) {
        $process = $this->exec("curl {$url}", true);

        return $process->isSuccessful() ? new Crawler($process->getOutput(), $url) : false;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function createAlbumDirectory($artist, $album) {
        // Create album directory
        $dir = sprintf(
            '%s%s%s - %s',
            $this->getSavePath(),
            DIRECTORY_SEPARATOR,
            strtoupper(str_replace(['-', '/'], '', $artist)),
            ucwords(strtolower(str_replace(['-', '/'], '', $album)))
        );

        if (!is_dir($dir)) {
            if (!@mkdir($dir)) {
                return false;
            }
        }

        return $dir;
    }

    /**
     * @param string $artist
     * @param string $track
     * @return string
     */
    protected function createSingleTrackPath($artist, $track) {
        // Create album directory
        return sprintf(
            '%s/%s - %s.mp3',
            $this->getSavePath(),
            strtoupper(str_replace(['-', '/'], '', $artist)),
            ucwords(strtolower(str_replace(['-', '/'], '', $track)))
        );
    }

    /**
     * @return string
     */
    protected function getSavePath() {
        return $this->container->getParameter('music_downloader.config')['save_path'];
    }

    /**
     * @param string $string
     * @return string
     */
    protected function clean($string) {
        return str_replace(['-', '/'], ' ', trim($string));
    }
}
