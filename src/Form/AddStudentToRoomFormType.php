<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class AddStudentToRoomFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'email',

            ])
            ->add('invite', SubmitType::class,[
                'label' => 'Добавить студента',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }


}
