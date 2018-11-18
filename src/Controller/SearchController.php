<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Studio;
use App\Entity\Style;
use App\Form\StudioSearch;
use App\Utils\RoutingUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/search")
 */
class SearchController extends Controller
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="search")
     */
    public function search(Request $request, RoutingUtils $routingUtils)
    {
        $studios = [];

        /**
         * @var Address $address
         */
        $address = new Address();

        /**
         * @var Style $style
         */
        $style = new Style();

        $formData = [
            'city' => $address,
            'style' => $style,
        ];

        $formData = $routingUtils->mergeSessionEntities('formData');

        $form = $this->createForm(
            StudioSearch::class,
            $formData
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $address = $formData['city'];
            $styles = $formData['style'];

            $styleIds = [];

            foreach ($styles as $style) {
                $styleIds[] = $style->getId();
            }

            $studios = $this->getDoctrine()
                ->getRepository(Studio::class)
                ->findByCityAndStyle($address->getCity(), $styleIds);
        }

        return new Response(
            $this->renderView('search/index.html.twig', [
                'form' => $form->createView(),
                'studios' => $studios,
            ])
        );
    }
}