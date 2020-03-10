<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('getMonth', ChoiceType::class, [
//                'choices' => $this->getMonth(date("m"), date("m", strtotime( "-1 month"))),
//                'data' => date("m", strtotime( "-1 month"))
//            ])
            ->add('getMonth', ChoiceType::class, [
                'choices' => $this->getMonth(1, 12),
                'data' => date("m", strtotime( "-1 month"))
            ])
            ->add('getYear', ChoiceType::class, [
                'choices' => $this->getYear(date("Y"), date("Y", strtotime( "-1 year"))),
                'data' => date("Y")
            ])
            ->add('submit', SubmitType::class, array(
                'label' => 'SUBMIT',
                'attr' => array('class' => 'btn btn-outline-primary mt-3')
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    private function getMonth($max, $min)
    {
        $month = range($max, (''.$min === 'current' ? date('m') : $min));
        $monthValue = [];
        $monthName = [];
        foreach ($month as $m => $val) {
            $monthValue[] = str_pad($val, 2, '0', STR_PAD_LEFT);
            $monthName[] = date('F', mktime(0, 0, 0, $val, 10));
        }
        return array_combine($monthName, $monthValue);
    }

    private function getYear($max, $min)
    {
        $year = range($max, (''.$min === 'current' ? date('Y') : $min));
        $yearValue = [];
        $yearName = [];
        foreach ($year as $t => $val) {
            $yearValue[] = $val;
            $yearName[] = date('Y', mktime(0, 0, 0, $val, 10));
        }
        return array_combine($yearValue, $yearValue);
    }
}
