<?php

namespace ApidaeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

            //-- Description longue
            ->add('objShowDescr', CheckboxType::class, array(
                'label' => 'Afficher la description longue',
                'required' => false))

            //-- Description personnalisée
            ->add('traDescriptionPersonnalisee', TextareaType::class, array(
                'label' => 'Description personnalisée'
            ))
            ->add('obj_show_descr_perso', CheckboxType::class, array(
                'label' => 'Afficher la description personnalisée',
                'required' => false))

            //-- Bons plans
            ->add('traBonsPlans', TextareaType::class, array(
                'label' => 'Bons plans'
            ))
            ->add('obj_show_bons_plans', CheckboxType::class, array(
                'label' => 'Afficher les bons plans',
                'required' => false))

            //-- Informations supplémentaires
            ->add('traInfosSup', TextareaType::class, array(
                'label' => 'Informations supplémentaires'
            ))
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
