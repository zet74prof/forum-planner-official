<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setRoles(['ROLE_USER'])
                ->setEmail($faker->email)
                ->setIsVerified(true)
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setUsername($faker->userName)
                ->setEnable2fa(false);

            $plainPassword = 'password123';
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $this->addReference('user_' . $i, $user);
            $manager->persist($user);

        }
        $manager->flush();
    }
}
