<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyUsersType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
     ->add('companyName', 'entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\CompanyDetails',
                'property' => 'CompanyName',
                'query_builder' => function($er){
                    return $er -> createQueryBuilder('a')
                                -> leftjoin('InitialShippingBundle:User','b','WITH','b.username = a.adminName')
                                ->where('b.id = :userId')
                                ->setParameter('userId',16);
                },
                'empty_value' =>false,
            ))
            ->add('userName')
            ->add('role', 'entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\UserRole',
                'property' => 'RoleName',
                'query_builder' => function($er){
                    return $er -> createQueryBuilder('a');
                },
                //'em' => 'client',
                'empty_value' =>false,
            ))
            ->add('emailId')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\CompanyUsers'
        ));
    }
}
