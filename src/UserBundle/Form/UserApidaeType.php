<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserApidaeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array('attr' => array('required' => true),
                'label_attr' => array('class' => 'inputForm')
                ))
            ->add('email', TextType::class, array('attr' => array('required' => true),
                'label_attr' => array('class' => 'inputForm')))
            ->add('roles', 'choice', array('choices' =>
                array(
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                ),
                'multiple' => true,
                'attr' => array('required' => true),
                'label_attr' => array('class' => 'inputForm')
            ))
            ->add('password', TextType::class, array('attr' => array('value' => "", 'required' => true),
                'label_attr' => array('class' => 'inputForm')))
            ->add('confirmerMdp', TextType::class, array('label' => 'Confirmez le mot de passe', "mapped" => false,
                'attr' => array('required' => true),
                'label_attr' => array('class' => 'inputForm')));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\UserApidae'
        ));
    }
}
