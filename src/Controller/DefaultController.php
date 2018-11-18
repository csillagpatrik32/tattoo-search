<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\StudioSearch;
use App\Utils\RoutingUtils;
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
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, RoutingUtils $routingUtils, SearchController $searchController)
    {
        $formData = $routingUtils->mergeSessionEntities('formData');

        $form = $this->createForm(
            StudioSearch::class,
            $formData
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search', [
                'request' => $request
            ], 307);
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