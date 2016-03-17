<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('username', TextType::class, array('required' => true))
            ->add('email', TextType::class, array('required' => true))
            ->add('roles', 'choice', array('choices' =>
                array(
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                ),
                'required'  => true,
                'multiple' => true
            ))
            ->add('password', TextType::class, array('attr' => array('value' => null, 'required' => true)))
            ->add('confirmerMdp', TextType::class, array('label' => 'Confirmez le mot de passe', "mapped" => false, 'required' => true))
            ->add('modifier', SubmitType::class, array('label' => 'Modifier'));
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
