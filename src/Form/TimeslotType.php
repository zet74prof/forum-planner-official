<?php

namespace App\Form;

use App\Entity\Stand;
use App\Entity\Team;
use App\Entity\Timeslot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeslotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDateTime', null, [
                'widget' => 'single_text',
            ])
            ->add('teams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('stands', EntityType::class, [
                'class' => Stand::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Timeslot::class,
        ]);
    }
}
