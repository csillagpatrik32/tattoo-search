<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudioRepository")
 */
class Studio
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Address", mappedBy="studio")
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="studios")
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Style", inversedBy="studios")
     */
    private $styles;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="studio", orphanRemoval=true)
     */
    private $employees;

    public function __construct()
    {
        $this->styles = new ArrayCollection();
        $this->employees = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Style[]
     */
    public function getStyles(): Collection
    {
        return $this->styles;
    }

    public function addStyle(Style $style)
    {
        if ($this->styles->contains($style)) {
            return;
        }

        $this->styles->add($style);
    }

    public function removeStyle(Style $style)
    {
        if (!$this->styles->contains($style)) {
            return;
        }

        $this->styles->removeElement($style);
    }

    /**
     * @return Collection|Employee[]
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee)
    {
        if ($this->employees->contains($employee)) {
            return;
        }

        $this->employees->add($employee);
    }

    public function removeEmployee(Employee $employee)
    {
        if (!$this->employees->contains($employee)) {
            return;
        }

        $this->employees->removeElement($employee);
    }
}
