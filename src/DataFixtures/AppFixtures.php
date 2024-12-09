<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

public function __construct(private readonly UserPasswordHasherInterface $hasher)
{

}

    public function load(ObjectManager $manager): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setRoles([$roles[$i % 2]])
            ->setEmail("user{$i}@fplanner.fr")
                ->setPassword($this->hasher->hashPassword($user, "password{$i}"))
                ->setUsername("user{$i}")
                ->setNom("Nom{$i}")
                ->setPrenom("Prenom{$i}")
                ->setIsVerified($i % 2 === 0);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
