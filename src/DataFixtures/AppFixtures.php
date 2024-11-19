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
        $user = new User();
        $user->setEmail('admin@doe.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setNom('Doe')
            ->setPrenom('John')
            ->setPassword($this->hasher->hashPassword($user, 'admin'))
            ->setUsername('admin')
            ->setIsVerified(true);
            //->setApiToken('admin_api_token');
        $manager->persist($user);

        $manager->flush();
    }
}
