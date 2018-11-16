<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Studio;
use App\Entity\Style;
use App\Entity\User;
use App\Form\AddStudio;
use App\Utils\RoutingUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/profile/studios")
 */
class StudioController extends Controller
{
    /**
     * @Route("/", name="profile_studios")
     */
    public function studios(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $studios = $this->getDoctrine()
            ->getRepository(Studio::class)
            ->findBy(['owner' => $user->getId()]);

        $studioData = [];

        /**
         * @var Studio $studio
         */
        foreach($studios as $key => $studio) {
            $studioData[$key]['Name'] = $studio->getName();
            $studioData[$key]['Address'] = implode(', ',$studio->getAddress()->toArray());
            $studioData[$key]['Styles'] = implode(', ',$studio->getStyle()->toArray());
        }

        return new Response($this->renderView(
            'profile/studio/index.html.twig',
            [
                'studioData' => $studioData
            ]
        ));
    }

    /**
     * @Route("/add", name="profile_add_new_studio")
     */
    public function addStudio(Request $request, TranslatorInterface $translator, RoutingUtils $routingUtils)
    {
        /**
         * @var Studio $studio
         */
        $studio = new Studio();

        /**
         * @var Address $address;
         */
        $address = new Address();

        /**
         * @var Style $style
         */
        $style = new Style();

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $formData = [
            'name' => '',
            'country' => '',
            'city' => '',
            'style' => $style,
        ];

        $formData = $routingUtils->mergeSessionEntities('formData');

        $form = $this->createForm(
            AddStudio::class,
            $formData
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $studioName = $formData['name'];
            $country = $formData['country'];
            $city = $formData['city'];
            $styles = $formData['style'];

            $studioNameCheck = $this->getDoctrine()
                ->getRepository(Studio::class)
                ->findOneBy(['name' => $studioName]);
            if ($studioNameCheck !== null) {
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
                $studio->setName($studioName);
                $studio->setOwner($user);

                foreach($styles as $style) {
                    $studio->addStyle($style);
                }

                $address->setCountry($country);
                $address->setCity($city);
                $address->setStudio($studio);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($address);
                $entityManager->persist($studio);
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'The new studio was added to your account'
                );

                return $this->redirectToRoute('profile_studios');
            }
        }

        return $this->render('profile/studio/add-new-studio.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}