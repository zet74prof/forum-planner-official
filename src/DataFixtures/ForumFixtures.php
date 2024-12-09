<?php

namespace App\DataFixtures;

use AllowDynamicProperties;
use App\Entity\Forum;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ForumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $forum = new Forum();
            $forum->setTitle('Forum ' . $faker->sentence(3));
            $forum->setDescription($faker->paragraph(3));
            $forum->setLocation($faker->city);
            $forum->setUser($this->getReference('user_' . $faker->numberBetween(0, 9)));

            $manager->persist($forum);
        }

        $manager->flush();
    }
}