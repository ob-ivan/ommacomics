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
            ->add('folder', FileType::class, ['label' => 'Select ZIP archive'])
            ->add('displayName', TextType::class, [
                'label' => 'Display name:',
                'required' => false,
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Publish now? (Your chapter will be available to anyone.)',
                'required' => false,
            ])
            ->add('save', SubmitType::class)
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
