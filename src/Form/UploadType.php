<?php
namespace App\Form;

use App\Entity\Chapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('folder', FileType::class, [
                'attr' => [
                    'accept' => 'application/zip, image/jpeg',
                    'class' => 'form__input',
                    'multiple' => 'multiple',
                ],
                'label' => 'Select ZIP or JPEG file(s)',
                'label_attr' => ['class' => 'form__label'],
                'multiple' => true,
                'row_attr' => ['class' => 'form__row'],
            ])
            ->add('displayName', TextType::class, [
                'attr' => ['class' => 'form__input'],
                'label' => 'Display name:',
                'label_attr' => ['class' => 'form__label'],
                'required' => false,
                'row_attr' => ['class' => 'form__row'],
            ])
            ->add('isPublic', CheckboxType::class, [
                'attr' => ['class' => 'form__input'],
                'label' => 'Publish now? (Your chapter will be available to anyone.)',
                'label_attr' => ['class' => 'form__label'],
                'required' => false,
                'row_attr' => ['class' => 'form__row form__row--checkbox'],
            ])
            ->add('isHorizontal', CheckboxType::class, [
                'attr' => ['class' => 'form__input'],
                'label' => 'Display in horizontal mode?',
                'label_attr' => ['class' => 'form__label'],
                'required' => false,
                'row_attr' => ['class' => 'form__row form__row--checkbox'],
            ])
            ->add('save', SubmitType::class, [
                'row_attr' => ['class' => 'form__row form__row--button'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
            'validation_groups' => ['upload'],
        ]);
    }
}
