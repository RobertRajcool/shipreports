<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ChartType extends AbstractType
{




    private $userId;

    public function __construct($id)
    {
        $this->userId = $id;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('kpiname','entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\KpiDetails',
                'property' => 'KpiName',
                'query_builder' => function($er){
                    return $er  -> createQueryBuilder('a')
                        ->leftjoin('InitialShippingBundle:ShipDetails','e','WITH','e.id = a.shipDetailsId')
                        ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = e.companyDetailsId')
                        ->leftjoin('InitialShippingBundle:CompanyUsers','c','WITH','b.id = c.companyName')
                        ->leftjoin('InitialShippingBundle:User','d','WITH','d.username = b.adminName or d.username = c.userName')
                        ->where('d.id = :userId')
                        ->groupby('a.kpiName')
                        ->setParameter('userId',$this->userId);
                },
                'empty_value' => '--Select KPI--', ))

            ->add('fromdate',   DateType::class,array('data'=> new \DateTime()))
            ->add('todate',   DateType::class,array('data'=> new \DateTime()))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\Chart'
        ));
    }
}
