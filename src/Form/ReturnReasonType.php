<?php

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\TestCsReturnReason;

class ReturnReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mailCode', TextType::class, [
                'mapped' => false
            ])
            ->add('reason', EntityType::class, [
                'class' => TestCsReturnReason::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.reasonTh', 'ASC');
                },
                'choice_label' => 'reasonTh',
                'choice_value' => 'recordId',
                'expanded' => true,
                'multiple' => false
            ])

            ->add('note', TextType::class, [
                'mapped' => false,
                'required' => false
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
