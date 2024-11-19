<?php

namespace App\DataFixtures;

use App\Entity\Stand;
use App\Entity\Forum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class StandFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Création d'un générateur Faker
        $faker = Factory::create('fr_FR');

        // Récupération des forums existants
        $forums = $manager->getRepository(Forum::class)->findAll();

        if (empty($forums)) {
            throw new \Exception('Aucun forum trouvé. Veuillez exécuter ForumFixtures d\'abord.');
        }

        // Génération de stands pour chaque forum
        foreach ($forums as $forum) {
            $numStands = $faker->numberBetween(1, 5); // Nombre de stands aléatoire par forum
            for ($i = 0; $i < $numStands; $i++) {
                $stand = new Stand();
                $stand->setTitle($faker->sentence(3)); // Titre réaliste
                $stand->setDescription($faker->paragraph(2)); // Description réaliste
                $stand->setCapacity($faker->numberBetween(10, 100)); // Capacité aléatoire
                $stand->setDuration($faker->dateTimeBetween('1 hour', '3 hours')); // Durée aléatoire
                $stand->setForum($forum); // Associe le forum

                $manager->persist($stand);
            }
        }

        // Sauvegarde des données
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ForumFixtures::class, // Dépendance aux forums créés dans ForumFixtures
        ];
    }
}
