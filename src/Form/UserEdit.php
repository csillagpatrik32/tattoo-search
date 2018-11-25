<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, [
            'constraints' => [
                new Assert\Length(['min' => 4, 'max' => 50]),
                new Assert\NotBlank()
            ]
        ])
            ->add('fullName', TextType::class, [
                'constraints' => [
                    new Assert\Length(['min' => 4, 'max' => 50]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('edit',SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}