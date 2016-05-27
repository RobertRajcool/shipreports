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
            ->add('shipType','entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\ShipTypes',
                'property' => 'ShipType',
                'empty_value' => '-- Select ShipType--'
            ))
            ->add('imoNumber')
            ->add('country','entity',array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\AppsCountries',
                'property' => 'CountryName',
                'empty_value' => '--Select Country--'
            ))
            ->add('location')
            ->add('companyDetailsId')
            ->add('description')
            ->add('built')
            ->add('size')
            ->add('gt')
            ->add('manufacturingYear')
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
