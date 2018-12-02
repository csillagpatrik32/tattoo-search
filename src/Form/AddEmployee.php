<?php

namespace App\Form;


use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddEmployee extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', EntityType::class,
                [
                    'class' => User::class,
                    'choice_label' => 'username',
                ])
            ->add('startDate', DateType::class,
                [
                    'label' => 'Start date',
                    'widget' => 'choice',
                ])
            ->add('endDate', DateType::class,
                [
                    'label' => 'End date',
                    'widget' => 'choice',
                    'required' => false,
                ])
            ->add('manager', CheckboxType::class,
                [
                    'label' => 'Manager',
                    'required' => false,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}