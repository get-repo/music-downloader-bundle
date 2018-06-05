<?php

namespace GetRepo\MusicDownloaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class MusicDownloaderCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('mad:download')
            ->addArgument('url', InputArgument::REQUIRED, 'URL of the album');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $configs = $this->getContainer()->getParameter('music_downloader.config');
        $url = $input->getArgument('url');

        foreach($configs['sites'] as $config) {
            if (preg_match($config['url'], $url)) {
                $this->fetch($config);
                return;
            }
        }

        throw new \LogicException("No site found for {$url}");
    }

    protected function fetch(array $config)
    {
        $container = $this->getContainer();
        $this->output->writeln("fetch from <comment>{$config['name']}</comment>");
        $parser = new $config['crawler_class'](
            $config,
            $container,
            $this->output
        );

        $parser->parse($this->input->getArgument('url'));
    }
}
