<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Sodium\add;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {

    }
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        // create 20 user! Bam!
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);
            $user->setUsername($faker->userName);
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $faker->password));
            $user->setRoles($faker->randomElement([['ROLE_USER'], ['ROLE_ADMIN']]));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
