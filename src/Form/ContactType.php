<?php

namespace App\Form;

use App\DTO\ContactDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'label' => 'Nom complet *',
                'attr' => ['placeholder' => 'Votre nom complet'],
            ])
            ->add('email', EmailType::class, [
                'empty_data' => '',
                'label' => 'Email *',
                'attr' => ['placeholder' => 'Votre email'],
            ])
            ->add('subject', TextType::class, [
                'empty_data' => '',
                'label' => 'Sujet',
                'attr' => ['placeholder' => 'Résumé de votre demande'],
            ])
            ->add('message', TextareaType::class, [
                'empty_data' => '',
                'label' => 'Message',
                'attr' => ['placeholder' => 'Décrivez votre demande en détail'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDTO::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
