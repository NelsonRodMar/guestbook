<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class CommentTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', TextType::class, [
                'label' => 'name.label',
                'required' => true,
                'attr' => ['autofocus' => true],
            ])
            ->add('text', TextareaType::class, [
                'label' => 'comment.label',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'email.label',
                'required' => true,
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => false,
                'constraints' => [
                    new Image(['maxSize' => '2048k']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'submit.button',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
