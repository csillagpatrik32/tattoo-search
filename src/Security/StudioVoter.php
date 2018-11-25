<?php

namespace App\Security;


use App\Entity\Studio;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StudioVoter extends Voter
{
    const DELETE = 'delete';
    const EDIT = 'edit';
    const VIEW = 'view';
    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
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

        return $studio->getOwner()->getId() === $authenticatedUser->getId();
    }

}