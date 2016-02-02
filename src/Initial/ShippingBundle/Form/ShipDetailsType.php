<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShipDetailsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipName')
            ->add('companyDetailsId', 'entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\CompanyDetails',
                'property' => 'CompanyName',
                'query_builder' => function($er){
                    return $er -> createQueryBuilder('a');
                },
                //'em' => 'client',
                'empty_value' =>false,
            ))
            ->add('description')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\ShipDetails'
        ));
    }
}
