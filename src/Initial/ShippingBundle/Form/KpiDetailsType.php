<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KpiDetailsType extends AbstractType
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
            ->add('shipDetailsId','entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\ShipDetails',
                'property' => 'ShipName',
                'multiple' => 'multiple',
                'query_builder' => function($er)
                {
                    if ($this->role == true)
                    {
                        return $er -> createQueryBuilder('a')
                            ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = a.companyDetailsId')
                            ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                            ->where('c.id = :userId')
                            ->setParameter('userId',$this->userId);
                    }
                    else
                    {
                        return $er -> createQueryBuilder('a')
                            ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = a.companyDetailsId')
                            ->where('b.id = :userId')
                            ->setParameter('userId',$this->userId);
                    }
                },
            ))
            ->add('kpiName')
            ->add('description')
            ->add('activeDate',  'hidden')
            ->add('endDate',  'hidden')
            ->add('cellName')
            ->add('cellDetails')
            ->add('weightage')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\KpiDetails'
        ));
    }
}