<?php
namespace Pim\Bundle\CatalogBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

/**
 * Load products samples
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProducts extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const SET_BASE          = 'base';
    const SET_GROUP_INFO    = 'general';
    const SET_GROUP_MEDIA   = 'media';
    const SET_GROUP_SEO     = 'seo';
    const SET_GROUP_TECHNIC = 'technical';
    const SET_ATT_SKU       = 'sku';
    const SET_ATT_NAME      = 'name';

    const SET_TSHIRT         = 'tshirt';
    const SET_GROUP_TSHIRT   = 'tshirt';
    const SET_ATT_SIZE       = 'tshirt_size';

    /**
    * @var ContainerInterface
    */
    protected $container;

    /**
     * ProductManager
     * @var ProductManager
     */
    protected $productManager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->productManager = $this->container->get('pim.catalog.product_manager');
        $baseSet = $this->createBaseSet();
        $tshirtSet = $this->createTshirtSet($baseSet);
        $this->createTshirtProducts($tshirtSet);
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * Create base product type
     */
    protected function createBaseSet()
    {
        // create product type
        $set = $this->productManager->getNewSetInstance();
        $set->setCode(self::SET_BASE);
        $set->setTitle('Default');

        // add groups
        $groups = array();
        $groupCodes = array(self::SET_GROUP_INFO, self::SET_GROUP_TECHNIC);
        foreach ($groupCodes as $code) {
            $group = $this->productManager->getNewGroupInstance();
            $group->setCode($code);
            $group->setTitle('Group '.$code);
            $set->addGroup($group);
        }
        $groupInfo = $set->getGroups()->first();
        $groupTechnic = $set->getGroups()->last();

        // add a attribute sku
        $attribute = $this->productManager->getNewAttributeInstance();
        $attribute->setCode(self::SET_ATT_SKU);
        $title = 'Sku';
        $attribute->setTitle($title);
        $attribute->setType(BaseFieldFactory::FIELD_STRING);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(true);
        $attribute->setValueRequired(true);
        $attribute->setSearchable(false);
        $attribute->setTranslatable(false);
        $this->productManager->getPersistenceManager()->persist($attribute);
        $groupInfo->addAttribute($attribute);

        // add a attribute name
        $attribute = $this->productManager->getNewAttributeInstance();
        $attribute->setCode(self::SET_ATT_NAME);
        $attribute->setTitle('Name');
        $attribute->setType(BaseFieldFactory::FIELD_STRING);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(false);
        $attribute->setValueRequired(true);
        $attribute->setSearchable(true);
        $attribute->setTranslatable(false);
        $this->productManager->getPersistenceManager()->persist($attribute);
        $groupInfo->addAttribute($attribute);

        // persist
        $this->productManager->getPersistenceManager()->persist($set);
        $this->productManager->getPersistenceManager()->flush();

        return $set;
    }

    /**
    * Create base product type
    */
    protected function createTshirtSet($baseSet)
    {
        // clone base product type
        $set = $this->productManager->cloneSet($baseSet);
        $set->setCode(self::SET_TSHIRT);
        $set->setTitle('T-shirt');

        // add a size attribute
        $attribute = $this->productManager->getNewAttributeInstance();
        $attribute->setCode(self::SET_ATT_SIZE);
        $attribute->setTitle('Size');
        $attribute->setType(BaseFieldFactory::FIELD_SELECT);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(false);
        $attribute->setValueRequired(false);
        $attribute->setSearchable(false);
        $attribute->setTranslatable(false);
        // add options
        $values = array('S', 'M', 'L', 'XL');
        $order = 1;
        foreach ($values as $value) {
            $option = $this->productManager->getNewAttributeOptionInstance();
            $option->setValue($value);
            $option->setSortOrder($order++);
            $attribute->addOption($option);
        }
        $this->productManager->getPersistenceManager()->persist($attribute);

        // add technical group
        $groupTechnic = $set->getGroup(self::SET_GROUP_TECHNIC);
        $groupTechnic->addAttribute($attribute);

        // persist
        $this->productManager->getPersistenceManager()->persist($set);
        $this->productManager->getPersistenceManager()->flush();

        return $set;
    }

    /**
     * Create products
     */
    protected function createTshirtProducts()
    {
        //$attSize = $this->productManager->getAttributeRepository()->findByCode(self::SET_ATT_SIZE);
        //$options = $att->getOptions();

        $set = $this->productManager->getSetRepository()->findOneByCode(self::SET_TSHIRT);
        $product = $this->productManager->getNewEntityInstance();
        $product->setSet($set);

        // create values
        $attSku = $this->productManager->getAttributeRepository()->findOneByCode(self::SET_ATT_SKU);
        $value = $this->productManager->getNewAttributeValueInstance();
        $value->setAttribute($attSku);
        $value->setData('my-sku-1');
        $product->addValue($value);

        $attName = $this->productManager->getAttributeRepository()->findOneByCode(self::SET_ATT_NAME);
        $value = $this->productManager->getNewAttributeValueInstance();
        $value->setAttribute($attName);
        $value->setData('My product name');
        $product->addValue($value);

        // persist product
        $this->productManager->getPersistenceManager()->persist($product);
        $this->productManager->getPersistenceManager()->flush();
    }

}
