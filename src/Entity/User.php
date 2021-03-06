<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="This e-mail is already used")
 * @UniqueEntity(fields="username", message="This username is already used")
 */
class User implements AdvancedUserInterface, \Serializable
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ARTIST = 'ROLE_ARTIST';
    const ROLE_MANAGER = 'ROLE_MANAGER';
    const ROLE_OWNER = 'ROLE_OWNER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=4, max=50)
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=254)
     */
    private $plainPassword = 12345678;

    /**
     * @ORM\Column(type="string", length=254, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(max=254)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(min=4, max=50)
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", nullable=true, length=30)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="string", nullable=true, length=30)
     */
    private $passwordResetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordResetTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;
    /**
     * @var array
     * @ORM\Column(type="simple_array")
     */
    private $roles;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserPreferences", cascade={"persist"})
     */
    private $preferences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Studio", mappedBy="owner")
     */
    private $studios;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="user")
     */
    private $employees;

    public function __construct()
    {
        $this->roles = [self::ROLE_USER];
        $this->enabled = false;
        $this->studios = new ArrayCollection();
        $this->employees = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->username;
    }

    /**
     * @return array
     */
    public function getAvailableRoles()
    {
        return [
            User::ROLE_USER,
            User::ROLE_ARTIST,
            User::ROLE_MANAGER,
            User::ROLE_OWNER,
            User::ROLE_ADMIN,
            User::ROLE_SUPERADMIN,
        ];
    }

    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->username,
                $this->password,
                $this->enabled
            ]
        );
    }

    public function unserialize($serialized)
    {
        list($this->id,
            $this->username,
            $this->password,
            $this->enabled
            ) = unserialize($serialized);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);

        if (in_array($role, $this->getAvailableRoles()) && !in_array($role, $this->roles)) {
            array_push($this->roles, $role);
        }
    }

    public function removeRole(string $role): void
    {
        $role = strtoupper($role);

        if (in_array($role, $this->roles)) {
            array_splice($this->roles, array_search($role, $this->roles), 1);
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    public function eraseCredentials()
    {

    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param mixed $confirmationToken
     */
    public function setConfirmationToken($confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return UserPreferences
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * @param mixed $preferences
     */
    public function setPreferences($preferences): void
    {
        $this->preferences = $preferences;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getPasswordResetToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * @param mixed $passwordResetToken
     */
    public function setPasswordResetToken($passwordResetToken): void
    {
        $this->passwordResetToken = $passwordResetToken;
    }

    /**
     * @return mixed
     */
    public function getPasswordResetTime()
    {
        return $this->passwordResetTime;
    }

    /**
     * @param mixed $passwordResetTime
     */
    public function setPasswordResetTime($passwordResetTime): void
    {
        $this->passwordResetTime = $passwordResetTime;
    }

    /**
     * @return Collection|Studio[]
     */
    public function getStudios(): Collection
    {
        return $this->studios;
    }

    /**
     * @return Collection|Employee[]
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }
}
