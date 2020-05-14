<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {

        $form = $this->createForm(RegistrationFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $student = $entityManager->getRepository(Student::class)->findOneBy(['email' => $form->get('email')->getData()]);
            $teacher = $entityManager->getRepository(Teacher::class)->findOneBy(['email' => $form->get('email')->getData()]);

            if (!empty($student) || !empty($teacher)) {
                $this->addFlash(
                    'danger',
                    'Пользователь с таким email уже существует!'
                );
            } else {
                $roleClass = "App\Entity\\" . $form->get('role')->getData();
                $newUser = new $roleClass();
                // encode the plain password
                $newUser->setPassword(
                    $passwordEncoder->encodePassword(
                        $newUser,
                        $form->get('plainPassword')->getData()
                    )
                );

                $newUser->setName($form->get('name')->getData());
                $newUser->setSurname($form->get('surname')->getData());
                $newUser->setEmail($form->get('email')->getData());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($newUser);
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'Вы успешно зарегистрировались в системе!'
                );
                return $this->redirectToRoute('app_login');
            }

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
