<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Security\RememberMe\DoctrineTokenProvider;

class CustomTokenService extends DoctrineTokenProvider
{
    public function __construct(EntityManagerInterface $em){
        parent::__construct($em->getConnection());
    }
}