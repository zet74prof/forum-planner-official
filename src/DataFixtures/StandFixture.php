<?php

namespace App\DataFixtures;

use App\Entity\Forum;
use App\Entity\Stand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class StandFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        $forumRepository = $manager->getRepository(Forum::class);
        $forum = $forumRepository->findAll();

        for ($i = 0; $i < 20; $i++) {
            $stand = new Stand();
            $stand->setTitle($faker->word);
            $stand->setDescription($faker->paragraph);
            $randomForum = $forum[array_rand($forum)];
            $stand->setForum($randomForum);
            $stand->setCapacity($faker->numberBetween(1, 100));
            $stand->setDuration($faker->dateTimeBetween('-21 days', '-7 days'));


            $manager->persist($stand);
        }

        $manager->flush();
    }
}
