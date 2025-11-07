<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label_attr' => ['class' => 'block text-lg text-gray-700'],
                'attr' => ['class' => 'w-full bg-[#fcfcf7] rounded-md p-4 mt-3',
                            'placeholder' => 'Votre adresse e-mail']
            ])
            ->add('subject', TextType::class, [
                'label_attr' => ['class' => 'block text-lg  text-gray-700'],
                'attr' => ['class' => 'w-full bg-[#fcfcf7] rounded-md p-4 mt-3',
                            'placeholder' => 'Votre sujet']
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'label_attr' => ['class' => 'block text-lg text-gray-700'],
                'attr' => ['class' => 'w-full bg-[#fcfcf7] p-4 rounded-md mt-3',
                            'placeholder' => 'Votre message',
                            'rows' => 5],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data-class' => Contact::class
        ]);
    }
}
