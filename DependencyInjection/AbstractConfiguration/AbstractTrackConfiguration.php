<?php

namespace GetRepo\MusicDownloaderBundle\DependencyInjection\AbstractConfiguration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

abstract class AbstractTrackConfiguration implements ConfigurationInterface
{
    /**
     * @return string
     */
    abstract protected function getArtistSelector();

    /**
     * @return string
     */
    abstract protected function getTrackSelector();

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('config');

        $rootNode
            ->children()
                ->arrayNode('selectors')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('artist')
                        ->cannotBeEmpty()
                        ->defaultValue($this->getArtistSelector())
                    ->end()
                    ->scalarNode('track')
                        ->cannotBeEmpty()
                        ->defaultValue($this->getTrackSelector())
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
