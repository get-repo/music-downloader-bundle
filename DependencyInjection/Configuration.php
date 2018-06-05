<?php

namespace GetRepo\MusicDownloaderBundle\DependencyInjection;

use Doctrine\Common\Util\Inflector;
use GetRepo\MusicDownloaderBundle\Crawler\AbstractCrawler\AbstractCrawler;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('music_downloader');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('save_path')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/..')
                ->end()
                ->arrayNode('sites')
                    ->beforeNormalization()
                        ->always()
                        ->then(function ($a) {
                            foreach((array) $a as $k => $v) {
                                $a[$k]['name'] = $k;
                                if (!isset($v['crawler_class'])) {
                                    $a[$k]['crawler_class'] = sprintf(
                                        "GetRepo\MusicDownloaderBundle\Crawler\%sCrawler",
                                        Inflector::classify($k)
                                    );
                                }
                                if (!isset($v['config_class'])) {
                                    $a[$k]['config_class'] = sprintf(
                                        "GetRepo\MusicDownloaderBundle\DependencyInjection\%sConfiguration",
                                        Inflector::classify($k)
                                    );
                                }
                                $a[$k]['config']['_config_class'] = $a[$k]['config_class'];
                            }

                            return $a;
                        })
                    ->end()
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('url')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->validate()
                                    ->ifTrue(function($regex) {
                                        return @preg_match($regex, 'whatever') === false;
                                    })
                                    ->thenInvalid("Invalid 'url' regular expression %surl")
                                ->end()
                            ->end()
                            ->arrayNode('fetchers')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->treatNullLike([])
                                ->arrayPrototype()
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->booleanNode('output')
                                            ->defaultValue(false)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('crawler_class')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->validate()
                                    ->ifTrue(function($class) {
                                        return !class_exists($class);
                                    })
                                    ->thenInvalid('Crawler Class %s not found')
                                ->end()
                                ->validate()
                                    ->ifTrue(function($class) {
                                        return !is_subclass_of(
                                            $class,
                                            AbstractCrawler::class
                                        );
                                    })
                                    ->thenInvalid(
                                        'Crawler Class %s does not extends ' .
                                        AbstractCrawler::class
                                    )
                                ->end()
                            ->end()
                            ->scalarNode('config_class')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->validate()
                                    ->ifTrue(function($class) {
                                        return !class_exists($class);
                                    })
                                    ->thenInvalid('Configuration Class %s not found')
                                ->end()
                                ->validate()
                                    ->ifTrue(function($class) {
                                        return !is_subclass_of(
                                            $class,
                                            ConfigurationInterface::class
                                        );
                                    })
                                    ->thenInvalid(
                                        'Configuration Class %s does not implements ' .
                                        ConfigurationInterface::class
                                    )
                                ->end()
                            ->end()
                            ->arrayNode('config')
                                ->variablePrototype()->end()
                                ->validate()
                                    ->always()
                                    ->then(function($values) {
                                        $class = $values['_config_class'];
                                        unset($values['_config_class']);

                                        return (new Processor())->processConfiguration(
                                            (new $class()),
                                            ['config' => $values]
                                        );
                                    })
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
