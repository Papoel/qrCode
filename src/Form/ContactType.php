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
            ->add(child: 'fullname', type: TextType::class, options: [
                'label' => 'Nom complet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom complet',
                    'id' => 'full-name',
                    'required' => true,
                ],
                'label_attr' => [
                    'for' => 'full-name',
                    'class' => 'floatingInput',
                ],
            ])
            ->add(child: 'email', type: EmailType::class, options: [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@exemple.com',
                    'id' => 'email',
                    'pattern' => '[^ @]*@[^ @]*',
                    'required' => true,
                ],
                'label_attr' => [
                    'for' => 'email',
                    'class' => 'floatingInput',
                ],
            ])
            ->add(child: 'message', type: TextareaType::class, options: [
                'label' => 'Message',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Describe message here',
                    'id' => 'message',
                ],
                'label_attr' => [
                    'for' => 'message',
                    'class' => 'floatingTextarea',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
