<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                // so user can't change its email
                'disabled' => true,
                'label'    => 'Email'
            ])
            ->add('firstname', TextType::class, [
                'disabled' => true,
                'label'    => 'Prénom'
            ])
            ->add('lastname', TextType::class, [
                'disabled' => true,
                'label'    => 'Nom'
            ])
            ->add('old_password', PasswordType::class, [
                'label'    => 'Ancien mot de passe',
                'mapped' => false,
                'attr'     => [
                    'placeholder'   => 'Saisissez votre ancien mot de passe'
                ]

            ])
            ->add('new_password', RepeatedType::class, [
                'type'  => PasswordType::class,
                // because it doesn't exist in entity, we need to add this
                'mapped' => false,
                'invalid_message'   => 'Le mot de passe et sa confirmation doivent être identique',
                'required'  => true,
                'first_options'     => [
                    'label' => 'Nouveau mot de passe',
                    'attr'  => [
                        'placeholder' => 'Saisissez votre nouveau mot de passe'
                    ]
                ],
                'second_options'     => [
                    'label' => 'Confirmation nouveau mot de passe',
                    'attr'  => [
                        'placeholder' => 'Confirmez votre nouveau mot de passe'
                    ]
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mettre à jour'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
