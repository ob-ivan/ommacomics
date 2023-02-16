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
                'attr' => ['class' => 'form__input'],
                'label' => 'Display name:',
                'label_attr' => ['class' => 'form__label'],
                'required' => false,
                'row_attr' => ['class' => 'form__row'],
            ])
            ->add('isPublic', CheckboxType::class, [
                'attr' => ['class' => 'form__input'],
                'label' => 'Publish now? (The chapter will be available to anyone.)',
                'label_attr' => ['class' => 'form__label'],
                'required' => false,
                'row_attr' => ['class' => 'form__row'],
            ])
            ->add('isHorizontal', CheckboxType::class, [
                'attr' => ['class' => 'form__input'],
                'label' => 'Display in horizontal mode?',
                'label_attr' => ['class' => 'form__label'],
                'required' => false,
                'row_attr' => ['class' => 'form__row'],
            ])
            ->add('save', SubmitType::class, [
                'row_attr' => ['class' => 'form__row form__row--button'],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $chapter = $event->getData();
                $form = $event->getForm();
                if ($chapter->getIsDeleted()) {
                    $form->add(
                        'restore',
                        SubmitType::class,
                        [
                            'attr'     => ['class' => 'edit-restore__button'],
                            'row_attr' => ['class' => 'edit-restore form__row form__row--button']
                        ]
                    );
                } else {
                    $form->add(
                        'delete',
                        SubmitType::class,
                        [
                            'attr'     => ['class' => 'edit-delete__button'],
                            'row_attr' => ['class' => 'edit-delete form__row form__row--button ']
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
