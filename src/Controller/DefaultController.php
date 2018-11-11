<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Style;
use App\Form\StudioSearch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager
    )
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $formData = [];

        if ($formData = $this->session->get('formData')) {
            foreach ($formData as $key => $data) {
                if (is_object($data)) {
                    $formData[$key] = $this->entityManager->merge($data);
                }
            }
        }

        $form = $this->createForm(
            StudioSearch::class,
            $formData
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->session->set('formData', $formData);

            return $this->redirectToRoute('search');
        }

        return new Response(
            $this->renderView('index/index.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    /**
     * @Route("/change-language/{lang}", name="change_lang")
     */
    public function changeLanguage(string $lang, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_USER'))
        {
            $user->getPreferences()->setLocale($lang);
            $this->entityManager->flush();
        }

        $this->session->set('_locale', $lang);

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/default", name="default")
     */
    public function default()
    {
        $html = $this->renderView('index/default.html.twig');

        return new Response($html);
    }
}