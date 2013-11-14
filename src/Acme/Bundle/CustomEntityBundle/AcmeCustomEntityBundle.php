<?php

namespace Acme\Bundle\CustomEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;

class AcmeCustomEntityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__ . '/Resources/config/doctrine/model') => 'Acme\Bundle\CustomEntityBundle\Model',
        );

        $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mappings, array('doctrine.orm.entity_manager'), 'acme_custom_entity.driver.doctrine/orm'));
        $container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mappings, array('doctrine.odm.mongodb.document_manager'), 'acme_custom_entity.driver.doctrine/mongodb-odm'));
    }
    
}
