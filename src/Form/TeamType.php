<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\Timeslot;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('members', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'multiple' => true,
            ])
            ->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
