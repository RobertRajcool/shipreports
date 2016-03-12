<?php

namespace Initial\ShippingBundle\Form;
use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('companyid','hidden')
            ->add('email')


            ->add('username')

            ->add('plainPassword', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\RepeatedType'), array(
                'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\PasswordType'),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('roles', 'collection', array(
                'type'   => 'choice',
                'options'  => array(
                    'choices'  => array(
                        'ROLE_ADMIN' => 'ROLE_ADMIN',
                        'ROLE_MANAGER' => 'ROLE_MANAGER',
                        'ROLE_REPORT_USER' => 'ROLE_REPORT_USER',
                        'ROLE_USER'    => 'ROLE_USER',
                        'ROLE_VPR_ADMIN'    => 'ROLE_VPR_ADMIN',
                    ),
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Initial\ShippingBundle\Entity\User'
        ));
    }
}
