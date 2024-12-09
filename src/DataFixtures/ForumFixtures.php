<?php

namespace App\DataFixtures;

use App\Entity\Forum;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ForumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création d'un générateur Faker
        $faker = Factory::create('fr_FR');

        // Récupérer les utilisateurs déjà existants depuis la base de données
        $users = $manager->getRepository(User::class)->findAll();

        // Vérification s'il y a des utilisateurs dans la base
        if (empty($users)) {
            // Si aucun utilisateur n'existe, il faudra ajouter un message ou en créer,
            echo "Aucun utilisateur trouvé. Assurez-vous d'avoir exécuté AppFixtures.php.";
            return;
        }

        // Génération de forums
        for ($i = 0; $i < 20; $i++) {
            $forum = new Forum();
            $forum->setTitle($faker->sentence(6)); // Titre avec environ 6 mots
            $forum->setDescription($faker->paragraph(3)); // Description réaliste
            $forum->setLocation($faker->city); // Ville aléatoire
            $forum->setUser($faker->randomElement($users)); // Associe un utilisateur aléatoire

            $manager->persist($forum);
        }

        // Sauvegarde des forums
        $manager->flush();
    }
}
