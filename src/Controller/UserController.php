<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserUpdate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        $html = $this->renderView('profile/profile.html.twig', [
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
                return $this->redirectToRoute('profile_update');
            }

            $user->setUsername($username);
            $user->setFullName($fullName);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $html = $this->renderView('profile/update.html.twig', [
            'form' => $form->createView()
        ]);

        return new Response($html);
    }
}