<?php

namespace App\DataFixtures;

use AllowDynamicProperties;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $user = new User();
        $user->setNom('seguin');
        $user->setPrenom('Evangelyne');
        $user->setUsername('eva');
        $user->setEmail('evangelyne.seguin@zet.fr');
        $user->setPassword('plainPassword');
        $user->setRoles(['ROLE_ADMIN']);
        $this->addReference('user_0', $user);
        $manager->persist($user);

        for ($i = 1; $i < 11; $i++) {
            $user = new User();
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);
            $user->setUsername($faker->userName);
            $user->setEmail($faker->email);
            $plainPassword = $faker->password;
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $this->addReference('user_' .$i, $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}