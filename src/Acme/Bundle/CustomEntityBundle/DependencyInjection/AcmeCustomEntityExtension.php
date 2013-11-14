<?php
namespace Acme\Bundle\CustomEntityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AcmeCustomEntityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $driver = $config['driver'];

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('driver/%s.yml', $driver));

        $container->setParameter($this->getAlias().'.driver', $driver);
        // Parameter defining if the mapping driver must be enabled or not
        $container->setParameter($this->getAlias().'.driver.'.$driver, true);
    }
}
