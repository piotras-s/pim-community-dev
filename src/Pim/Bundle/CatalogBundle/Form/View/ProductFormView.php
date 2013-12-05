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
     * @return array
     */
    public function getGroups(FormView $view)
    {
        $groups = array();
        foreach ($view['values'] as $fieldView) {
            $this->setFieldValue($groups, $fieldView);
        }

        foreach ($groups as &$group) {
            ksort($group['orderedAttributes']);
            foreach ($group['orderedAttributes'] as $attributes) {
                $group['attributes'] += $attributes;
            }
            unset($group['orderedAttributes']);
        }

        return $groups;
    }

    /**
     * Sets an attribute's values inside an array of groups
     *
     * @param array    $groups
     * @param FormView $view
     */
    protected function setFieldValue(array &$groups, FormView $view)
    {
        $attribute = $this->getAttribute($view);
        $group = $attribute->getVirtualGroup();
        $groupId = $group->getId();
        $order = $attribute->getSortOrder();
        if (!isset($groups[$groupId]['orderedAttributes'][$order])) {
            if (!isset($groups[$groupId])) {
                $groups[$groupId] = array();
                $groups[$groupId] = array(
                    'label'                 => $group->getLabel(),
                    'orderedAttributes'     => array(),
                    'attributes'            => array()
                );
            }
            $groups[$groupId]['orderedAttributes'][$order] = array();
        }
        
        $value = $view->vars['value'];
        $groups[$groupId]['orderedAttributes'][$order][$value->getId()] = $this->getValueView(
            $value,
            $attribute,
            $view
        );
    }

    /**
     * Returns the attribute corresponding to a view
     *
     * @param  FormView         $view
     * @return ProductAttribute
     */
    protected function getAttribute(FormView $view)
    {
        foreach ($view as $valueView) {
            if (isset($valueView['flexibleAttribute'])) {
                return $valueView->vars['flexibleAttribute'];
            }
        }
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
    protected function getValueView(ProductValueInterface $value, ProductAttribute $attribute, FormView $view)
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
