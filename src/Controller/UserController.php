<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserPasswordChange;
use App\Form\UserUpdate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/profile")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="profile")
     */
    public function profile()
    {
        $user = $this->getUser();

        $profileData = [
            'Username' => $user->getUsername(),
            'Email' => $user->getEmail(),
            'Full name' => $user->getFullName()
        ];

        return new Response($this->renderView(
            'profile/index.html.twig', [
                'user' => $user,
                'profileData' => $profileData
            ])
        );
    }

    /**
     * @Route("/update", name="profile_update")
     */
    public function profileUpdate(
        Request $request,
        TranslatorInterface $translator
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $formData = ['username' => $user->getUsername(), 'fullName' => $user->getFullName()];

        $form = $this->createForm(
            UserUpdate::class,
            $formData
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $username = $formData['username'];
            $fullName = $formData['fullName'];

            $usernameCheck = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['username' => $username]);
            if ($usernameCheck !== null and $username !== $user->getUsername()) {
                $form->get('username')->addError(
                    new FormError(
                        $translator->trans('This username is already used', [], 'validators')
                    )
                );
            } else {
                $user->setUsername($username);
                $user->setFullName($fullName);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Your profile has been updated');

                return $this->redirectToRoute('profile');
            }
        }

        return new Response($this->renderView(
            'profile/update.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    /**
     * @Route("/password-change", name="profile_password")
     */
    public function passwordChange(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $formData = ['oldPassword' => '', 'plainPassword' => ''];

        $form = $this->createForm(
            UserPasswordChange::class,
            $formData
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $plainPassword = $formData['plainPassword'];

            $password = $passwordEncoder->encodePassword(
                $user,
                $plainPassword
            );
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Your password has changed');

            return $this->redirectToRoute('profile');
        }

        return new Response($this->renderView(
            'profile/password-change.html.twig', [
                'form' => $form->createView()
            ])
        );
    }
}