<?php

namespace App\Form;

use App\Data\SearchData;
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
            ->add('city', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' =>[
                    'placeholder' => 'Ville'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' =>[
                    'placeholder' => 'Pays'
                ],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    //pour que l'url soit propore
    public function getBlockPrefix()
    {
        return '';
    }
}
