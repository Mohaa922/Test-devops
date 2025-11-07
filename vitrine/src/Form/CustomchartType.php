<?php

namespace App\Form;

use App\Entity\CustomChart;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class CustomchartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('colors',
                type: LiveCollectionType::class, options:['entry_type' => ThemeColorType::class]
            )
            ->add('buttonType')
            ->add('fonts', type:LiveCollectionType::class, options:['entry_type' => ThemeFontType::class])
           // ->add('fonts')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomChart::class,
            'csrf_protection' => false
        ]);
    }
}
