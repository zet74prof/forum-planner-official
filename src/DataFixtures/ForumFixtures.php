<?php

namespace App\DataFixtures;

use App\Entity\Forum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ForumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 10; $i++) {
            $forum = new Forum();
            $forum->setTitle($faker->sentence(3))
                ->setDescription($faker->paragraph(5))
                ->setLocation($faker->city);

            $randomUserIndex = $faker->numberBetween(0, 9);
            $forum->setUser($this->getReference('user_' . $randomUserIndex));

            $manager->persist($forum);
        }

        $manager->flush();
    }

    // Déclare que ForumFixtures dépend de UserFixtures
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
