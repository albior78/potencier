<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AdminFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $admin = new Admin;

        $admin
            ->setEmail('ebacour78@gmail.com')
            ->setUsername('ebacour')
            ->setPassword('$2y$13$xW1jCRJyLSXoeVUvMAraJegbwr8y6Ex3Cy4usCqKzEpuUplhg3tIS')
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();
    }
}
