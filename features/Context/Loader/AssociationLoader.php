<?php

namespace Context\Loader;

use Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadAssociationData;

/**
 * Loader for associations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationLoader extends LoadAssociationData
{
    /**
     * @var string Path of the fixtures file
     */
    protected $filePath;

    /**
     * @param string $filePath
     *
     * @return AssociationLoader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
