<?php

namespace Acme\Bundle\CustomEntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root   = $builder
            ->root('acme_custom_entity')
            ->children()
                ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $builder;
    }
}
