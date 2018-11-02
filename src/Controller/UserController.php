<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserPasswordChange;
use App\Form\UserUpdate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/profile")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="profile")
     */
    public function profile(Request $request)
    {
        $user = $this->getUser();

        $html = $this->renderView('profile/index.html.twig', [
            'user' => $user
        ]);

        return new Response($html);
    }

    /**
     * @Route("/update", name="profile_update")
     */
    public function profileUpdate(Request $request)
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
                $this->addFlash('danger', 'This username is already used');
                return $this->redirectToRoute('profile');
            }

            $user->setUsername($username);
            $user->setFullName($fullName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Your profile has been updated');

            return $this->redirectToRoute('profile');
        }

        $html = $this->renderView('profile/update.html.twig', [
            'form' => $form->createView()
        ]);

        return new Response($html);
    }

    /**
     * @Route("/password-change", name="profile_password")
     */
    public function passwordChange(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var User $user */
        $user = $this->getUser();

        $formData = ['plainPassword' => ''];

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

        $html = $this->renderView('profile/password-change.html.twig', [
            'form' => $form->createView()
        ]);

        return new Response($html);
    }
}