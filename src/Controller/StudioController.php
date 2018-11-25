<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Employee;
use App\Entity\Studio;
use App\Entity\Style;
use App\Entity\User;
use App\Form\AddEmployee;
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

        $studios = $user->getStudios();

        $studioData = [];

        /**
         * @var Studio $studio
         */
        foreach($studios as $key => $studio) {
            $studioData[$key]['Id'] = $studio->getId();
            $studioData[$key]['Name'] = $studio->getName();
            $studioData[$key]['Address'] = $studio->getAddress();
            $studioData[$key]['Styles'] = $studio->getStyles();
        }

        return new Response($this->renderView(
            'profile/studios/index.html.twig',
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
                    'The new studios was added to your account'
                );

                return $this->redirectToRoute('profile_studios');
            }
        }

        return $this->render('profile/studios/add.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="profile_studio_edit")
     * @Security("is_granted('edit', studio)")
     */
    public function edit(Studio $studio, Request $request, TranslatorInterface $translator, RoutingUtils $routingUtils)
    {
        $formData = [
            'name' => $studio->getName(),
            'country' => $studio->getAddress()->getCountry(),
            'city' => $studio->getAddress()->getCity(),
            'style' => $studio->getStyles(),
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

        return $this->render('profile/studios/edit.html.twig',
            [
                'form' => $form->createView(),
                'studio' => $studio,
            ]
        );
    }

    /**
     * @Route("/{id}", name="profile_studio")
     * @Security("is_granted('edit', studio)")
     */
    public function single(Studio $studio, Request $request, RoutingUtils $routingUtils)
    {
        return $this->render('profile/studios/single.html.twig',
            [
                'studio' => $studio,
            ]
        );
    }

    /**
     * @Route("/add-employee/{id}", name="profile_studio_add_employee")
     * @Security("is_granted('edit', studio)")
     */
    public function addEmployee(Studio $studio, Request $request, TranslatorInterface $translator)
    {
        $employee = new Employee();

        $form = $this->createForm(
            AddEmployee::class,
            []
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $user = $formData['user'];
            $startDate = $formData['startDate'];
            $endDate = $formData['endDate'];

            $checkUniqueEmployee = $this->getDoctrine()
                ->getRepository(Employee::class)
                ->checkUniqueEmployee($user, $studio, $startDate, $endDate);

            if ($checkUniqueEmployee) {
                $form->get('user')->addError(
                    new FormError(
                        $translator->trans(
                            'This user is already an employee for this period',
                            [],
                            'validators'
                        )
                    )
                );
            } else {
                $employee->setUser($user);
                $employee->setStudio($studio);
                $employee->setStartDate($startDate);
                $employee->setEndDate($endDate);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($employee);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'The employee was added to the studio'
                );

                return $this->redirectToRoute('profile_studio', ['id' => $studio->getId()]);
            }
        }

        return $this->render('profile/studios/employee/add.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/edit-employee/{id}", name="profile_studio_edit_employee")
     */
    public function editEmployee(Employee $employee, Request $request, TranslatorInterface $translator)
    {
        $studio = $employee->getStudio();

        $this->denyAccessUnlessGranted('edit', $studio);

        $form = $this->createForm(
            AddEmployee::class,
            [
                'user' => $employee->getUser(),
                'startDate' => $employee->getStartDate(),
                'endDate' => $employee->getEndDate(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $user = $formData['user'];
            $startDate = $formData['startDate'];
            $endDate = $formData['endDate'];

            $checkUniqueEmployee = $this->getDoctrine()
                ->getRepository(Employee::class)
                ->checkUniqueEmployee($user, $studio, $startDate, $endDate, $employee->getId());

            if ($checkUniqueEmployee) {
                $form->get('user')->addError(
                    new FormError(
                        $translator->trans(
                            'This user is already an employee for this period',
                            [],
                            'validators'
                        )
                    )
                );
            } else {
                $employee->setUser($user);
                $employee->setStudio($studio);
                $employee->setStartDate($startDate);
                $employee->setEndDate($endDate);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($employee);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'The employee was updated'
                );

                return $this->redirectToRoute('profile_studio', ['id' => $studio->getId()]);
            }
        }

        return $this->render('profile/studios/employee/edit.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/delete-employee/{id}", name="profile_studio_delete_employee")
     */
    public function deleteEmployee(Employee $employee, Request $request, TranslatorInterface $translator)
    {
        $studio = $employee->getStudio();

        $this->denyAccessUnlessGranted('delete', $studio);

        $studio->removeEmployee($employee);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($studio);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'The employee was deleted'
        );

        return $this->redirectToRoute('profile_studio', ['id' => $studio->getId()]);
    }
}