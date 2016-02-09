<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KpiDetailsType extends AbstractType
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
            ->add('shipDetailsId','entity', array(
        'required' => true,
        'class' => 'Initial\ShippingBundle\Entity\ShipDetails',
        'property' => 'ShipName',
        'multiple' => true,
        'query_builder' => function($er){
            return $er -> createQueryBuilder('a')
                        ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = a.companyDetailsId')
                        ->leftjoin('InitialShippingBundle:CompanyUsers','c','WITH','b.id = c.companyName')
                        ->leftjoin('InitialShippingBundle:User','d','WITH','d.username = b.adminName or d.username = c.userName')
                        ->where('d.id = :userId')
                        ->setParameter('userId',$this->userId);
        },
        'empty_value' => '--Select Ship--',
    ))

            ->add('kpiName')
            ->add('description')
            ->add('activeDate', 'date')
            ->add('endDate', 'date')
            ->add('cellName')
            ->add('cellDetails')
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


