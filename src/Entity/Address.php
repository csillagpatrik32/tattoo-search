<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 */
class Address
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Studio", inversedBy="address")
     */
    private $studio;

    /**
     * @ORM\Column(type="string")
     */
    private $country;

    /**
     * @ORM\Column(type="string")
     */
    private $city;

    public function __toString()
    {
        return $this->getFullAddress();
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        return $this->city.', '.$this->country;
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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): self
    {
        $this->city = $city;

        return $this;
    }
}
