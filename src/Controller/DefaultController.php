<?php

namespace App\Controller;

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
    public function index()
    {
        $html = $this->renderView('index/index.html.twig');

        return new Response($html);
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

        return $this->redirectToRoute('index');
    }
}