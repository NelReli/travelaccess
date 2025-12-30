<?php

namespace App\Form;

use App\DTO\SearchDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' =>[
                    'placeholder' => 'Rechercher des destinations'
                ]
            ])
            ->add('city', ChoiceType::class, [
                'choices' => $options['cities'],   // injecté depuis le contrôleur
                'choice_label' => fn($city) => $city,
                'label' => false,
                'placeholder' => 'Toutes les villes', 
                'required' => false,
                'multiple' => false,
                'expanded' => false, // false = <select>
            ])
            ->add('country', ChoiceType::class, [
                'choices' => $options['countries'],   // injecté depuis le contrôleur
                'choice_label' => fn($country) => $country,
                'label' => false,
                'placeholder' => 'Tous les pays', 
                'required' => false,
                'multiple' => false,
                'expanded' => false, // false = <select>
            ])
            ->add('order', ChoiceType::class, [
                'label' => false,
                'required' => false,                
                'placeholder' => 'Trier par',
                'choices' => [
                    'Les plus vus' => 'views',
                    'Les plus commentés' => 'comments',
                    'Accessibilité' => 'accessibility',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchDTO::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'cities' => [],
            'countries' => [],
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }

    //pour que l'url soit propre
    public function getBlockPrefix(): string
    {
        return '';
    }
}
