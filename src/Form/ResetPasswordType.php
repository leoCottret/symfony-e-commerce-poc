<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('password', RepeatedType::class, [
            'type'  => PasswordType::class,
            'invalid_message'   => 'Le mot de passe et sa confirmation doivent être identique',
            'label' => 'Mot de passe',
            'required'  => true,
            'first_options'     => [
                'label' => 'Mot de passe',
                'attr'  => [
                    'placeholder' => 'Saisissez votre mot de passe'
                ]
            ],
            'second_options'     => [
                'label' => 'Confirmation mot de passe',
                'attr'  => [
                    'placeholder' => 'Confirmez votre mot de passe'
                ]
            ]
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Mettre à jour',
            'attr' => [
                'class' => 'btn'
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
