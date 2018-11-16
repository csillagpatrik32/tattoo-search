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
     * @ORM\OneToMany(targetEntity="App\Entity\Address", mappedBy="studio")
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="studio")
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Style", inversedBy="studio")
     */
    private $style;

    public function __construct()
    {
        $this->style = new ArrayCollection();
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Collection
     */
    public function getStyle()
    {
        return $this->style;
    }

    public function addStyle(Style $style)
    {
        if ($this->style->contains($style)) {
            return;
        }

        $this->style->add($style);
    }

    public function removeStyle(Style $style)
    {
        if (!$this->style->contains($style)) {
            return;
        }

        $this->style->removeElement($style);
    }
}
