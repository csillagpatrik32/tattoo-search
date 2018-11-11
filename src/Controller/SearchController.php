<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Studio;
use App\Entity\Style;
use App\Form\StudioSearch;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/search")
 */
class SearchController extends Controller
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
     * @Route("/", name="search")
     */
    public function search(Request $request)
    {
        $address = new Address();
        $style = new Style();

        $formData = [];

        if ($formData = $this->session->get('formData')) {
            foreach ($formData as $key => $data) {
                if (is_object($data)) {
                    $formData[$key] = $this->entityManager->merge($data);
                }
            }
            $address = $formData['city'];
            $style = $formData['style'];
        }

        $form = $this->createForm(
            StudioSearch::class,
            $formData
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $address = $formData['city'];
            $style = $formData['style'];

            $this->session->set('formData', $formData);
        }

        $studios = $this->getDoctrine()
            ->getRepository(Studio::class)
            ->findByCityAndStyle($address->getCity(), $style->getName());

        return new Response(
            $this->renderView('search/index.html.twig', [
                'form' => $form->createView(),
                'studios' => $studios,
            ])
        );
    }
}