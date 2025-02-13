<?php

namespace App\Form;

use App\Entity\AppTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount')
            ->add('status')
            ->add('creatAt')
            ->add('updateAt')
            ->add('association')
            ->add('relation')
            ->add('enterprise')
            ->add('bank')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppTransaction::class,
        ]);
    }
}
