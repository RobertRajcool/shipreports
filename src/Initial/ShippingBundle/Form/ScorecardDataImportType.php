<?php
/**
 * Created by PhpStorm.
 * User: hari
 * Date: 29/6/16
 * Time: 11:16 AM
 */

namespace Initial\ShippingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ScorecardDataImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('fileName', FileType::class)
            ->add('monthDetail', DateType::class, array('data' => new \DateTime()))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\ScorecardDataImport'
        ));
    }

    public function getName() {
        return 'initial_shipping_scorecard_data_import';
    }
}
