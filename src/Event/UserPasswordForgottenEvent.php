<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserPasswordForgottenEvent extends Event
{
    const NAME = 'user.password_forgotten';

    /**
     * @var User
     */
    private $forgottenPasswordUser;

    public function __construct(User $forgottenPasswordUser)
    {
        $this->forgottenPasswordUser = $forgottenPasswordUser;
    }

    /**
     * @return User
     */
    public function getForgottenPasswordUser(): User
    {
        return $this->forgottenPasswordUser;
    }
}