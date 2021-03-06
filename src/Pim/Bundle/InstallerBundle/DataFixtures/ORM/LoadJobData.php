<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Load fixtures for jobs
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadJobData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['jobs'])) {
            foreach ($configuration['jobs'] as $code => $data) {
                $job = $this->createJob($code, $data);
                $this->validate($job, $data);
                $manager->persist($job);
            }

            $manager->flush();
        }
    }

    /**
     * @param string $code
     * @param array  $data
     *
     * @return JobInstance
     */
    protected function createJob($code, array $data)
    {
        $job = new JobInstance($data['connector'], $data['type'], $data['alias']);
        $job->setCode($code);
        $job->setLabel($data['label']);
        $job->setType($data['type']);
        $job->setRawConfiguration($data['configuration']);

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'jobs';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 160;
    }
}
