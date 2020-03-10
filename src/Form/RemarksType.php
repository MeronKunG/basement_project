<?php

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\TestCsRemarksProcess;
use App\Entity\TestCsRemarksStatus;

class RemarksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mailCode', TextType::class, [
                'mapped' => false
            ])
            ->add('process', EntityType::class, [
                'class' => TestCsRemarksProcess::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('tp')
                        ->where('tp.processActive = 1')
                        ->orderBy('tp.processOrder', 'ASC');
                },
                'choice_label' => 'processName',
                'choice_value' => 'processName'
            ])
            ->add('status', EntityType::class, [
                'class' => TestCsRemarksStatus::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ts')
                        ->where('ts.statusActive = 1')
                        ->orderBy('ts.statusOrder', 'ASC');
                },
                'choice_label' => 'statusName',
                'expanded' => true,
                'multiple' => false
            ])

            ->add('note', TextType::class, [
                'mapped' => false
            ])

            ->add('submit', SubmitType::class, array(
                'label' => 'SUBMIT',
                'attr' => array('class' => 'btn btn-outline-primary mt-3')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
