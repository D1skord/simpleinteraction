<?php

namespace App\Form;

use App\Entity\Teacher;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Имя',
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('surname', TextType::class, [
                'label' => 'Фамилия',
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('email', TextType::class, [
                'label' => 'Почтовый адрес',
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'label' => 'Пороль',
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('role', ChoiceType::class, [
                    'label' => 'Выберите роль',
                    'choices' => [
                        'Ученик' => 'Student',
                        'Преподаватель' => 'Teacher'
                    ],
                    'row_attr' => [
                        'class' => 'form-group'
                    ],
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add('save', SubmitType::class, [
                'label' => 'Зарегистрироваться',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }


}
