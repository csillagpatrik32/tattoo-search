<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmployeeRepository")
 */
class Employee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="employees")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Studio", inversedBy="employees")
     */
    private $studio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $manager;

    public function __construct()
    {
        $this->isManager = false;
    }

    public function __toString()
    {
        $endDate = ($this->endDate) ? ' to '.$this->endDate->format('Y-m-d') : '';
        $manager = ($this->manager) ? ' (Manager) ' : ' ';

        return $this->user->getFullName().$manager.'from '.$this->startDate->format('Y-m-d').$endDate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): self
    {

        $this->user = $user;

        return $this;
    }

    /**
     * @return Studio
     */
    public function getStudio(): ?Studio
    {
        return $this->studio;
    }

    /**
     * @param mixed $studio
     */
    public function setStudio($studio): self
    {
        $this->studio = $studio;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param mixed $manager
     */
    public function setManager($manager): self
    {
        $this->manager = $manager;

        return $this;
    }
}
