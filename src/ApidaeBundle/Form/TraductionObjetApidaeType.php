<?php

namespace ApidaeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraductionObjetApidaeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objet', ObjetApidaeType::class)
            ->add('traDescriptionPersonnalisee')
            ->add('obj_show_descr_perso', CheckboxType::class, array(
                'label' => 'Afficher la description personnalisée',
                'required' => false))

            ->add('traBonsPlans')
            ->add('obj_show_bons_plans', CheckboxType::class, array(
                'label' => 'Afficher les bons plans',
                'required' => false))

            ->add('traInfosSup')
            ->add('obj_show_info_sup', CheckboxType::class, array(
                'label' => 'Afficher les informations supplémentaires',
                'required' => false))

        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ApidaeBundle\Entity\TraductionObjetApidae'
        ));
    }
}
