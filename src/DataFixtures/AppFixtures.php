<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Flex\Recipe;
use Faker\Factory;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@Forum.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setUsername('admin')
            ->setNom('Bonhomme')
            ->setPrenom('Mathis')
            ->setEnable2fa(false)
            ->setIsVerified(true)
            ->setPassword($this->passwordHasher->hashPassword($user, 'admin'));


        $faker = Factory::create('fr_FR');
        $faker->addProvider(new User($faker));

        for ($i = 1; $i < 10; $i++){
            $recipe= (new User())
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setEmail($faker->lastName() . '@Forum.fr')
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setIsVerified(true)
                ->setRoles(['ROLE_USER'])
                ->setEnable2fa(false)
                ->setUsername($faker->username());
            $manager->persist($recipe);
        }

        $manager->persist($user);

        $manager->flush();
    }
}
