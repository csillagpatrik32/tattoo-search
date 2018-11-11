<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Studio;
use App\Entity\Style;
use App\Entity\User;
use App\Entity\UserPreferences;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const USERS = [
        [
            'username' => 'super_admin',
            'email' => 'super_admin@admin.com',
            'password' => 'admin1234',
            'fullName' => 'Super Admin',
            'roles' => [User::ROLE_ADMIN]
        ],
        [
            'username' => 'test',
            'email' => 'test@test.com',
            'password' => 'password123',
            'fullName' => 'Test Account',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'john_doe',
            'email' => 'john_doe@doe.com',
            'password' => 'john123',
            'fullName' => 'John Doe',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'rob_smith',
            'email' => 'rob_smith@smith.com',
            'password' => 'rob12345',
            'fullName' => 'Rob Smith',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'marry_gold',
            'email' => 'marry_gold@gold.com',
            'password' => 'marry12345',
            'fullName' => 'Marry Gold',
            'roles' => [User::ROLE_USER]
        ],
    ];

    private const LANGUAGES = [
        'en',
        'de',
        'hu'
    ];

    private const STUDIOS = [
        [
            'owner_id' => 0,
            'name' => 'Test München Tattoo Studio',
            'style_id' => [0, 1, 2, 3],
        ],
        [
            'owner_id' => 1,
            'name' => 'Test Hamburg Tattoo Studio',
            'style_id' => [0, 1, 3],
        ],
        [
            'owner_id' => 2,
            'name' => 'John Tattoo Studio',
            'style_id' => [0, 1],
        ],
    ];

    private const ADDRESSES = [
        [
            'studio_id' => 0,
            'country' => 'Germany',
            'city' => 'München',
        ],
        [
            'studio_id' => 1,
            'country' => 'Germany',
            'city' => 'Hamburg',
        ],
        [
            'studio_id' => 2,
            'country' => 'Hungary',
            'city' => 'Budapest',
        ],
    ];

    private const STYLES = [
        [
            'name' => 'Minimalist'
        ],
        [
            'name' => 'Geometric'
        ],
        [
            'name' => 'Line'
        ],
        [
            'name' => 'Dotwork'
        ],
    ];

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadStyles($manager);
        $this->loadStudios($manager);
        $this->loadAddresses($manager);
    }

    private function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setFullName($userData['fullName']);
            $user->setEmail($userData['email']);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $userData['password']
                )
            );
            $user->setRoles($userData['roles']);
            $user->setEnabled(true);

            $this->addReference(
                $userData['username'],
                $user
            );

            $preferences = new UserPreferences();
            $preferences->setLocale((self::LANGUAGES[rand(0, 2)]));

            $user->setPreferences($preferences);

            $manager->persist($user);
        }
        $manager->flush();
    }

    private function loadStudios(ObjectManager $manager)
    {
        foreach (self::STUDIOS as $studioData) {
            $studio = new Studio();
            $studio->setName($studioData['name']);

            $studio->setOwner($this->getReference(
                self::USERS[$studioData['owner_id']]['username']
            ));

            foreach ($studioData['style_id'] as $styleData) {
                $studio->addStyle($this->getReference(
                    self::STYLES[$styleData]['name']
                ));
            }

            $this->addReference(
                $studioData['name'],
                $studio
            );

            $manager->persist($studio);
        }

        $manager->flush();
    }

    private function loadAddresses(ObjectManager $manager)
    {
        foreach (self::ADDRESSES as $addressData) {
            $address = new Address();
            $address->setStudio($this->getReference(
                self::STUDIOS[$addressData['studio_id']]['name']
            ));

            $address->setCountry($addressData['country']);
            $address->setCity($addressData['city']);

            $manager->persist($address);
        }

        $manager->flush();
    }

    private function loadStyles(ObjectManager $manager)
    {
        foreach (self::STYLES as $styleData) {
            $style = new Style();
            $style->setName($styleData['name']);

            $this->addReference(
                $styleData['name'],
                $style
            );

            $manager->persist($style);
        }

        $manager->flush();
    }
}