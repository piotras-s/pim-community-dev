<?php                                                                                                                               
namespace Acme\Bundle\CustomEntityBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class ManufacturerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');
        $builder->add('name', null, array('required' => false));
        $builder->add('country');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Acme\Bundle\CustomEntityBundle\Model\Manufacturer'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'acme_custom_entity_manufacturer';
    }
}
