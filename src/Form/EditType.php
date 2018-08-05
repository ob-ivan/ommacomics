<?php
namespace App\Form;

use App\Entity\Chapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Publish now? (The chapter will be available to anyone.)',
                'required' => false,
            ])
            ->add('isDeleted', CheckboxType::class, [
                'label' => 'Delete chapter? (The chapter will not be available any more.)',
                'required' => false,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
        ]);
    }
}
