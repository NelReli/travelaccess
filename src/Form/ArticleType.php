<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'empty_data' => '',
                'label' => 'Titre de votre expérience *',
                'attr' => ['placeholder' => 'Ex: Le Louvre : Un musée accessible à tous'],
            ])
            ->add('description', TextareaType::class, [
                'empty_data' => '',
                'label' => 'Votre expérience',
                'attr' => ['placeholder' => 'Racontez votre expérience en détail. Intégrez naturellement les informations d\'accéssibilité : comment vous êtes arrivé, les équipements disponibles, les difficultés rencontrées, vos conseils...'],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville *',
                'attr' => ['placeholder' => 'Ex: Paris'],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays *',
                'attr' => ['placeholder' => 'Ex: France'],
            ])
            ->add('images', FileType::class, [
                'label' => 'Photos (vous pouvez en sélectionner plusieurs)',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '10M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ],
                        'mimeTypesMessage' => 'Merci d\'uploader un fichier JPG, PNG ou WebP valide',
                        ])
                    ])
                ],
            ])
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    'Aucune étoile (pas accessibile)' => 0,
                    '1 étoile (très difficile)' => 1,
                    '2 étoiles (difficile)' => 2,
                    '3 étoiles (modéré)' => 3,
                    '4 étoiles (bon)' => 4,
                    '5 étoiles (facile)' => 5,
                ],
                'expanded' => false,
                'multiple' => false,
                'label' => 'Accessibilité *',
                'placeholder' => "Note d'accessibilité (0-5 étoiles)",
                'required' => false,
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
