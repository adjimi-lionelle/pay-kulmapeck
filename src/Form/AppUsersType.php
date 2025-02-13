<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use App\Entity\AppUsers;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AppUsersType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('userName')
            ->add('password')
            ->add('surName')
            ->add('enable')
            ->add('email')
            ->add('phone')
            ->add('firstName')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => AppUsers::class, 
            'csrf_protection' => false,
        ]);
    }
}

