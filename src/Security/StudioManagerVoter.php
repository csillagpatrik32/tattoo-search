<?php

namespace App\Security;


use App\Entity\Employee;
use App\Entity\Studio;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StudioManagerVoter extends Voter
{
    const DELETE = 'delete';
    const EDIT = 'edit';
    const VIEW = 'view';
    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManagerInterface $entityManager)
    {
        $this->decisionManager = $decisionManager;
        $this->entityManager = $entityManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::DELETE, self::EDIT, self::VIEW])) {
            return false;
        }

        if (!$subject instanceof Studio) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, [User::ROLE_ADMIN])) {
            return true;
        }

        $authenticatedUser = $token->getUser();

        if (!$authenticatedUser instanceof User) {
            return false;
        }

        /**
         * @var Studio $studio
         */
        $studio = $subject;

        $employee = $this->entityManager->getRepository(Employee::class)->findBy([
            'user' => $authenticatedUser->getId(),
            'studio' => $studio->getId(),
            'manager' => true,
        ]);

        return $employee;
    }

}