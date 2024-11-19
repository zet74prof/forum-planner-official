<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create();
        $faker->addProvider(new User($faker));

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email)
                ->setRoles(['ROLE_USER'])
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setPassword($this->hasher->hashPassword($user, $faker->password))
                ->setUsername($faker->username)
                ->setIsVerified(true);

            $manager->persist($user);
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
