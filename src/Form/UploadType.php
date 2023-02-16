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
                    'multiple' => 'multiple'
                ],
                'label' => 'Select ZIP or JPEG file(s)',
                'multiple' => true,
            ])
            ->add('displayName', TextType::class, [
                'label' => 'Display name:',
                'required' => false,
            ])
            ->add('isPublic', CheckboxType::class, [
                'row_attr' => [
                    'class' => 'upload-form__checkbox-row',
                ],
                'label' => 'Publish now? (Your chapter will be available to anyone.)',
                'required' => false,
            ])
            ->add('isHorizontal', CheckboxType::class, [
                'row_attr' => [
                    'class' => 'upload-form__checkbox-row',
                ],
                'label' => 'Display in horizontal mode?',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'row_attr' => [
                    'class' => 'upload-form__button-row',
                ],
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
