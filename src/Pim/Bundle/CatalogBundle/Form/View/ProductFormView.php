<?php

namespace Pim\Bundle\CatalogBundle\Form\View;

use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Form\FormView;

/**
 * Custom form view for Product form
 * This class allows to group ProductValue fields in order to use them easily in the templates
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormView
{
    /**
     * @var array A list of the attribute types for which creating a new option is allowed
     */
    private $choiceAttributeTypes = array(
        'pim_catalog_multiselect',
        'pim_catalog_simpleselect'
    );

    /**
     * Returns an array of groups containing all attribute fields data
     *
     * @param FormView $view
     *
     * @return array
     */
    public function getGroups(FormView $view)
    {
        $groupsIndex = array();
        foreach ($view['values'] as $fieldView) {
            $this->buildIndex($groupsIndex, $fieldView);
        }
        krsort($groupsIndex);

        $groups = array();
        foreach ($groupsIndex as $groupIndex) {
            foreach ($groupIndex as $groupId => $groupInfo) {
                $groups[$groupId] = $this->getGroupView($groupInfo);
            }
        }

        return $groups;
    }

    /**
     * Returns the view for a group
     *
     * @param array $groupInfo
     *
     * @retur array
     */
    protected function getGroupView(array $groupInfo)
    {
        $view = array(
            'label'      => $groupInfo['label'],
            'attributes' => array()
        );

        ksort($groupInfo['attributes']);
        foreach ($groupInfo['attributes'] as $attributes) {
            $view['attributes'] += $attributes;
        }

        return $view;
    }

    /**
     * Sets an attribute's values inside an array of indexed groups
     *
     * @param array    $groups
     * @param FormView $view
     */
    protected function buildIndex(array &$groups, FormView $view)
    {
        $value = $view->vars['value'];
        $attribute = $value->getAttribute();
        $group = $attribute->getVirtualGroup();
        $groupId = $group->getId();
        $groupOrder = $group->getSortOrder();
        $attributeOrder = $attribute->getSortOrder();
        if (!isset($groups[$groupOrder][$groupId]['attributes'][$attributeOrder])) {
            if (!isset($groups[$groupOrder][$groupId])) {
                if (!isset($groups[$groupOrder])) {
                    $groups[$groupOrder] = array();
                }
                $groups[$groupOrder][$groupId] = array(
                    'label'      => $group->getLabel(),
                    'attributes' => array()
                );
            }
            $groups[$groupOrder][$groupId]['attributes'][$attributeOrder] = array();
        }

        $groups[$groupOrder][$groupId]['attributes'][$attributeOrder][$attribute->getId()] = $this->getAttributeView(
            $value,
            $attribute,
            $view
        );
    }

    /**
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    protected function getAttributeClasses(ProductAttribute $attribute)
    {
        $classes = array();
        if ($attribute->getScopable()) {
            $classes['scopable'] = true;
        }

        if ($attribute->getTranslatable()) {
            $classes['translatable'] = true;
        }

        if ('pim_catalog_price_collection' === $attribute->getAttributeType()) {
            $classes['currency'] = true;
        }

        return $classes;
    }

    /**
     * @param ProductValueInterface $value
     * @param ProductAttribute      $attribute
     * @param FormView              $view
     *
     * @return array
     */
    protected function getAttributeView(ProductValueInterface $value, ProductAttribute $attribute, FormView $view)
    {
        $attributeView = array(
            'isRemovable'        => $value->isRemovable(),
            'code'               => $attribute->getCode(),
            'label'              => $attribute->getLabel(),
            'sortOrder'          => $attribute->getSortOrder(),
            'allowValueCreation' => in_array($attribute->getAttributeType(), $this->choiceAttributeTypes)
        );

        if ($attribute->getScopable()) {
            $attributeView['values'] = array_merge(
                $this->getAttributeValues($attribute),
                array($value->getScope() => $view)
            );
        } else {
            $attributeView['value'] = $view;
        }

        $classes = $this->getAttributeClasses($attribute);
        if (!empty($classes)) {
            $attributeView['classes'] = $classes;
        }

        return $attributeView;
    }

    /**
     * @param ProductAttribute $attribute
     *
     * @return ArrayCollection
     */
    protected function getAttributeValues(ProductAttribute $attribute)
    {
        $group = $attribute->getVirtualGroup();
        if (!isset($this->view[$group->getId()]['attributes'][$attribute->getId()]['values'])) {
            return array();
        }

        return $this->view[$group->getId()]['attributes'][$attribute->getId()]['values'];
    }
}
