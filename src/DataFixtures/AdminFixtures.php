<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new Admin;

        $admin
            ->setEmail('ebacour78@gmail.com')
            ->setUsername('ebacour')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'the_new_password'))
            ->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);
        $manager->flush();
    }
}
