<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;



class ProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'disabled' => true, // Affiche mais non modifiable
            ])
            ->add('lastname', TextType::class, [
                'disabled' => true, 
            ])
            ->add('username', TextType::class, [
                'disabled' => true,
            ])
            ->add('email', EmailType::class, [
                'disabled' => true, 
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Votre avatar (JPG, PNG ou WebP)',
                'mapped' => false, // important car on gère nous-mêmes l'upload
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Merci de télécharger une image JPG, PNG ou WebP valide',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['data-turbo' => 'false'],
        ]);
    }
}