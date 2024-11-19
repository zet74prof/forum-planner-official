<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@forumplanner.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin'));
        $admin->setIsVerified(true);
        $admin->setUsername('osamakhait');
        $admin->setNom('Khait');
        $admin->setPrenom('Osama');
        $manager->persist($admin);

        // Création d'un générateur Faker
        $faker = Factory::create('fr_FR'); // Utiliser 'fr_FR' pour des données localisées en français

        // Génération de 20 utilisateurs aléatoires
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setIsVerified($faker->boolean(80)); // 80% de chances d'être vérifié
            $user->setUsername($faker->unique()->userName);
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);

            $manager->persist($user);
        }

        // Sauvegarde des données
        $manager->flush();
    }
}
