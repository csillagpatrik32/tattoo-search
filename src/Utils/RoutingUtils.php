<?php

namespace App\Utils;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RoutingUtils
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    /**
     * When Entities are passed to the session they need to be merged by the entityManager
     *
     * @param string $sessionAttributes
     * @return array
     */
    public function mergeSessionEntities(string $sessionAttributes)
    {
        if ($formData = $this->session->get($sessionAttributes)) {
            foreach ($formData as $key => $data) {
                if (is_object($data)) {
                    $formData[$key] = $this->entityManager->merge($data);
                }
            }
        }

        return $formData;
    }
}