<?php

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubElementDetailsType extends AbstractType
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
            ->add('elementDetailsId','entity', array(
                'required' => true,
                'class' => 'Initial\ShippingBundle\Entity\ElementDetails',
                'property' => 'ElementName',
                'query_builder' => function($er){
                    return $er  -> createQueryBuilder('a')
                                ->leftjoin('InitialShippingBundle:KpiDetails','f','WITH','f.id = a.kpiDetailsId')
                                ->leftjoin('InitialShippingBundle:ShipDetails','e','WITH','e.id = f.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = e.companyDetailsId')
                                ->leftjoin('InitialShippingBundle:CompanyUsers','c','WITH','b.id = c.companyName')
                                ->leftjoin('InitialShippingBundle:User','d','WITH','d.username = b.adminName or d.username = c.userName')
                                ->where('d.id = :userId')
                                ->setParameter('userId',$this->userId);
                },
                //'em' => 'client',
                'empty_value' =>false,
            ))
            ->add('subElementName')
            ->add('description')
            ->add('cellName')
            ->add('cellDetails')
            ->add('activatedDate', 'date')
            ->add('endDate', 'date')
            ->add('weightage')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\SubElementDetails'
        ));
    }
}
