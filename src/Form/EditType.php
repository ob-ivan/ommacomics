<?php
namespace App\Form;

use App\Entity\Chapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayName', TextType::class, [
                'label' => 'Display name:',
                'required' => false,
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Publish now? (The chapter will be available to anyone.)',
                'required' => false,
            ])
            ->add('isHorizontal', CheckboxType::class, [
                'label' => 'Display in horizontal mode?',
                'required' => false,
            ])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $chapter = $event->getData();
                $form = $event->getForm();
                if ($chapter->getIsDeleted()) {
                    $form->add(
                        'restore',
                        SubmitType::class,
                        [
                            'attr'     => ['class' => 'edit-restore__button'],
                            'row_attr' => ['class' => 'edit-restore']
                        ]
                    );
                } else {
                    $form->add(
                        'delete',
                        SubmitType::class,
                        [
                            'attr'     => ['class' => 'edit-delete__button'],
                            'row_attr' => ['class' => 'edit-delete']
                        ]
                    );
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
        ]);
    }
}
