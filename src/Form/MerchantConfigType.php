<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MerchantConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('merType', ChoiceType::class, [
                'choices' => [
                    'PARCEL' => 'parcel',
                    'HOLDING+HYBRID' => 'holding',
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('check', ChoiceType::class, [
                'choices' => [
                    'yes' => 'yes', // not null โทรแล้ว
                    'no' => 'no', // null ยังไม่ได้โทร
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('dateTime', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('billing', ChoiceType::class, [
                'choices' => [
                    'COD' => 'cod',
                    'NORMAL' => 'normal',
                    'COD+NORMAL' => 'codnormal',
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('submit', SubmitType::class, array(
                'label' => 'Submit',
                'attr' => array('class' => 'btn btn-outline-secondary  mt-3 float-right')
            ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
