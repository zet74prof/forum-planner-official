<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Forum;
use App\Entity\User;

class ForumFixtures extends Fixture
{



    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll();

        for ($i = 0; $i < 20; $i++) {
            $forum = new Forum();
            $randomUser = $users[array_rand($users)];
            $forum->setUser($randomUser);
            $forum->setDescription($faker->paragraph);
            $forum->setTitle($faker->word);
            $forum->setLocation($faker->city);

            $manager->persist($forum);
        }

        $manager->flush();

    }
}
