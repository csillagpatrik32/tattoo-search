<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserPasswordForgottenEvent;
use App\Form\UserPasswordForgotten;
use App\Form\UserPasswordReset;
use App\Repository\UserRepository;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if (!$this->getUser()) {
            return new Response(
                $this->renderView(
                    'security/login.html.twig',
                    [
                        'last_username' => $authenticationUtils->getLastUsername(),
                        'error' => $authenticationUtils->getLastAuthenticationError()
                    ]
                )
            );
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {

    }

    /**
     * @Route("/confirm/{token}", name="security_confirm")
     */
    public function confirm(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $user = $userRepository->findOneBy([
            'confirmationToken' => $token
        ]);

        if ($user === null) {
            $this->addFlash('danger', 'Something went wrong. Please check your email.');
            return $this->redirectToRoute('default');
        }
        $user->setEnabled(true);
        $user->setConfirmationToken('');

        $entityManager->flush();

        return new Response($this->renderView(
            'security/confirmation.html.twig',
                [
                    'user' => $user
                ]
            )
        );
    }

    /**
     * @Route("/password-forgotten", name="security_password_forgotten")
     */
    public function passwordForgotten(
        Request $request,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        TokenGenerator $tokenGenerator
    )
    {
        $formData = ['email' => ''];

        $form = $this->createForm(
            UserPasswordForgotten::class,
            $formData
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $email = $formData['email'];

            $userFound = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);
            if ($userFound === null) {
                $form->get('email')->addError(
                    new FormError(
                        $translator->trans('This email was not found', [], 'validators')
                    )
                );
            } else {
                $userFound->setPasswordResetToken($tokenGenerator->getRandomSecureToken(30));
                $userFound->setPasswordResetTime(new \DateTime());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($userFound);
                $entityManager->flush();

                $userPasswordForgottenEvent = new UserPasswordForgottenEvent($userFound);
                $eventDispatcher->dispatch(
                    UserPasswordForgottenEvent::NAME,
                    $userPasswordForgottenEvent
                );

                $this->addFlash('success', 'Please check your email');

                return $this->redirectToRoute('default');
            }
        }

        return new Response($this->renderView(
            'security/password-forgotten.html.twig',
                [
                    'form' => $form->createView()
                ]
            )
        );
    }

    /**
     * @Route("/password-reset/{token}", name="security_password_reset")
     */
    public function passwordReset(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $user = $userRepository->findOneBy([
            'passwordResetToken' => $token
        ]);

        if ($user !== null) {
            /* Check if token is still valid, if not then let the user know */
            $resetTime = new \DateTime($user->getPasswordResetTime()->format('Y-m-d H:i:s'));
            $now = new \DateTime();
            $diff = $now->diff($resetTime);
            $hours = $diff->h;
            $hours = $hours + ($diff->days*24);
            if ($hours > 24) {
                $this->addFlash('danger', 'This token is no longer available. Please reset your password again.');
                return $this->redirectToRoute('security_password_forgotten');
            }

            /* Token is valid, reset token and render password change form */
            $formData = ['plainPassword' => ''];

            $form = $this->createForm(
                UserPasswordReset::class,
                $formData
            );
            $form->handleRequest($request);

            /** If form is valid then reset the Password token and time,
             *  then send them to the login page and let them know their password has changed
             */
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $plainPassword = $formData['plainPassword'];

                $password = $passwordEncoder->encodePassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($password);

                $user->setPasswordResetToken(null);
                $user->setPasswordResetTime(null);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Your password has changed');

                return $this->redirectToRoute('security_login');
            }

            return new Response($this->renderView(
                'security/password-reset.html.twig', [
                    'form' => $form->createView()
                ])
            );
        }

        return $this->redirectToRoute('default');
    }
}