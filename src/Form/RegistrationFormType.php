<?php
namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'Nombre',
                    'required' => true
                ]
            ])
            ->add('apellidos', TextType::class,  [
                'label' => 'Apellidos',
                'attr' => [
                    'placeholder' => 'Apellidos',
                    'required' => true
                ]
            ])
            ->add('email', EmailType::class,  [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                    'required' => true
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => [
                    'placeholder' => 'Password',
                    'required' => true
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Guardar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
