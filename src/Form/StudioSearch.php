<?php

namespace App\Form;


use App\Entity\Address;
use App\Entity\Style;
use App\Repository\AddressRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudioSearch extends AbstractType
{
    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('city', EntityType::class,
            [
                'class' => Address::class,
                'choices' => $this->addressRepository->distinctCities(),
                'choice_label' => 'city'
            ])
            ->add('style', EntityType::class,
                [
                    'class' => Style::class,
                    'label' => 'Styles',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                ])
            ->add('search',SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}