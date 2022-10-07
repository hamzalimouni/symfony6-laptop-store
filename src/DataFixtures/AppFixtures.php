<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $PasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->PasswordHasher = $userPasswordHasherInterface;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $password = "admin1234";
        $hashedPassword = $this->PasswordHasher->hashPassword($user, $password);
        $user->setUsername('admin');
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        $manager->flush();
    }
}
