<?php

namespace App\Form;


use App\Entity\Style;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddStudio extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class,
                [
                    'label' => 'Studio Name'
                ])
            ->add('country', TextType::class)
            ->add('city', TextType::class)
            ->add('style', EntityType::class,
                [
                    'class' => Style::class,
                    'label' => 'Styles',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}