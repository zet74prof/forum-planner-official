<?php

namespace App\DataFixtures;

use AllowDynamicProperties;
use App\Entity\Forum;
use App\Entity\Stand;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class StandFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $stand = new Stand();
            $stand->setTitle('Stand ' . $faker->sentence(3));
            $stand->setDescription($faker->paragraph(3));
            $stand->setCapacity($faker->numberBetween(1, 10));
            $stand->setDuration($faker->dateTimeBetween('1 hour', '3 hours'));
            $stand->setForum($this->getReference('forum_' . $faker->numberBetween(0, 4)));

            $manager->persist($stand);
        }

        $manager->flush();
    }
}