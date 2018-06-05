<?php

namespace GetRepo\MusicDownloaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class BandcampAlbumConfiguration implements ConfigurationInterface
{
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
                        ->defaultValue('span[itemprop=byArtist]')
                    ->end()
                    ->scalarNode('album')
                        ->cannotBeEmpty()
                        ->defaultValue('.trackTitle[itemprop=name]')
                    ->end()
                    ->scalarNode('tracks')
                        ->cannotBeEmpty()
                        ->defaultValue('#track_table .title a[itemprop=url]')
                    ->end()
                    ->scalarNode('cover')
                        ->cannotBeEmpty()
                        ->defaultValue('#tralbumArt img[itemprop=image]')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
