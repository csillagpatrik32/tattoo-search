<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserPasswordChange;
use App\Form\UserEdit;
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
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $profileData = [
            'Username' => $user->getUsername(),
            'Email' => $user->getEmail(),
            'Full name' => $user->getFullName(),
        ];

        return new Response($this->renderView(
            'profile/user/index.html.twig',
            [
                'user' => $user,
                'profileData' => $profileData
            ]
        ));
    }

    /**
     * @Route("/edit", name="profile_edit")
     */
    public function profileEdit(Request $request, TranslatorInterface $translator)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $formData = [
            'username' => $user->getUsername(),
            'fullName' => $user->getFullName()
        ];

        $form = $this->createForm(
            UserEdit::class,
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
                        $translator->trans(
                            'This username is already used',
                            [],
                            'validators'
                        )
                    )
                );
            } else {
                $user->setUsername($username);
                $user->setFullName($fullName);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'Your profile has been updated'
                );

                return $this->redirectToRoute('profile');
            }
        }

        return new Response($this->renderView(
            'profile/user/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        ));
    }

    /**
     * @Route("/password-change", name="profile_password")
     */
    public function passwordChange(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $formData = [
            'oldPassword' => '',
            'plainPassword' => ''
        ];

        $form = $this->createForm(
            UserPasswordChange::class,
            $formData
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $plainPassword = $formData['plainPassword'];

            /**
             * @var User $user
             */
            $user = $this->getUser();

            $password = $passwordEncoder->encodePassword(
                $user,
                $plainPassword
            );
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your password has changed'
            );

            return $this->redirectToRoute('profile');
        }

        return new Response($this->renderView(
            'profile/user/password-change.html.twig',
            [
                'form' => $form->createView()
            ]
        ));
    }

    /**
     * @Route("/get-artist-profile", name="profile_get_artist")
     */
    public function getArtist(Request $request)
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ARTIST')) {
            $this->addFlash(
                'danger',
                'You already have an artist profile'
            );

            return $this->redirectToRoute('profile');
        }

        $user->addRole('ROLE_ARTIST');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your account was updated'
        );

        return $this->redirectToRoute('security_logout');
    }

    /**
     * @Route("/remove-artist-profile", name="profile_remove_artist")
     */
    public function removeArtist(Request $request)
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ARTIST')) {
            $this->addFlash(
                'danger',
                'You do not have an artist profile'
            );

            return $this->redirectToRoute('profile');
        }

        $user->removeRole('ROLE_ARTIST');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your account was updated'
        );

        return $this->redirectToRoute('security_logout');
    }

    /**
     * @Route("/get-owner-profile", name="profile_get_owner")
     */
    public function getOwner(Request $request)
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_OWNER')) {
            $this->addFlash(
                'danger',
                'You already have an owner profile'
            );

            return $this->redirectToRoute('profile');
        }

        $user->addRole('ROLE_OWNER');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your account was updated'
        );

        return $this->redirectToRoute('security_logout');
    }
}