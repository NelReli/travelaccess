<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom *',
                'attr' => ['placeholder' => 'Votre prénom'],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom *',
                'attr' => ['placeholder' => 'Votre nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email *',
                'attr' => ['placeholder' => 'votre.email@exemple.com'],
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur *',
                'required' => true,
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio (optionnel)',
                'required' => false,
                'attr' => ['placeholder' => 'Parlez-nous un peu de vous...'],
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (image JPG ou PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Merci d\'uploader une image valide (JPEG ou PNG)',
                    ])
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe *',
                    'attr' => ['placeholder' => 'Votre mot de passe'],
                ],
                'second_options' => [
                    'label' => 'Confirmer mot de passe *',
                    'attr' => ['placeholder' => 'Confirmez votre mot de passe'],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
                'label_html' => true, // important pour autoriser les liens HTML
                'label' => 'J\'accepte <a class="text-clear" href="/terms" target="_blank">les conditions d\'utilisation</a> et <a class="text-clear" href="/mentions" target="_blank">la politique de confidentialité</a>.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}