<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setFirstname(firstname: 'cyril');
        $user->setEmail(email: 'cyril@cyrcrea.com');
        $plainPassword = 'admin';
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setRoles(roles: ['ROLE_ADMIN']);

        $manager->persist($user);
        $manager->flush();
    }
}
