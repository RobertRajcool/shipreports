<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementDetailsType extends AbstractType
{

    private $userId;

    public function __construct($id,$role)
    {
        $this->userId = $id;
        $this->role = $role;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('kpiDetailsId','entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\KpiDetails',
                'property' => 'KpiName',
                'query_builder' => function($er)
                {
                    if ($this->role == true) {
                        return $er -> createQueryBuilder('a')
                            ->leftjoin('InitialShippingBundle:ShipDetails','e','WITH','e.id = a.shipDetailsId')
                            ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = e.companyDetailsId')
                            ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                            ->where('c.id = :userId')
                            ->groupby('a.kpiName')
                            ->setParameter('userId',$this->userId);
                    } else {
                        return $er -> createQueryBuilder('a')
                            ->leftjoin('InitialShippingBundle:ShipDetails','e','WITH','e.id = a.shipDetailsId')
                            ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = e.companyDetailsId')
                            ->where('b.id = :userId')
                            ->groupby('a.kpiName')
                            ->setParameter('userId',$this->userId);
                    }
                },
                'empty_value' =>"--Select KPI--",
            ))
            ->add('elementName')
            ->add('description')
            ->add('cellName')
            ->add('cellDetails')
            ->add('activatedDate', 'date')
            ->add('endDate', 'date')
            ->add('weightage')
            ->add('rules','hidden')
            ->add('vesselWiseTotal', 'choice', array(
                'choices' => array(
                    'Average' => 'Average',
                    'Sum' => 'Sum'
                ),
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'label' => false,
                'data' => 'Average'
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\ElementDetails'
        ));
    }
}