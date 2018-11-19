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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
            $studioData[$key]['Id'] = $studio->getId();
            $studioData[$key]['Name'] = $studio->getName();
            $studioData[$key]['Address'] = $studio->getAddress();
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
    public function add(Request $request, TranslatorInterface $translator, RoutingUtils $routingUtils)
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
                $form->get('name')->addError(
                    new FormError(
                        $translator->trans(
                            'This name is already used',
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

        return $this->render('profile/studio/add.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/{id}", name="profile_studio_update")
     * @Security("is_granted('update', studio)")
     */
    public function update(Studio $studio, Request $request, TranslatorInterface $translator, RoutingUtils $routingUtils)
    {
        $formData = [
            'name' => $studio->getName(),
            'country' => $studio->getAddress()->getCountry(),
            'city' => $studio->getAddress()->getCity(),
            'style' => $studio->getStyle(),
        ];

        //$formData = $routingUtils->mergeSessionEntities('formData');

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
            if ($studioNameCheck !== null and $studioName !== $studio->getName()) {
                $form->get('name')->addError(
                    new FormError(
                        $translator->trans(
                            'This name is already used',
                            [],
                            'validators'
                        )
                    )
                );
            } else {
                $studio->setName($studioName);
                $studio->getAddress()->setCountry($country);
                $studio->getAddress()->setCity($city);

                foreach($styles as $style) {
                    $studio->addStyle($style);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($studio);
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'The studio was updated'
                );

                return $this->redirectToRoute('profile_studios');
            }
        }

        return $this->render('profile/studio/update.twig',
            [
                'form' => $form->createView(),
                'studio' => $studio,
            ]
        );
    }
}