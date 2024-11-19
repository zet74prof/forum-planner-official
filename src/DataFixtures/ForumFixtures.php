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
        $faker->addProvider(new Forum($faker));

        $titles = ['Rubber ducky', 'phising', 'brute force'];

        for ($i = 1; $i < 10; $i++){
            $recipe= (new Forum())
                ->setTitle($faker->randomElement($titles))
                ->setDescription($faker->text())
                ->setLocation($faker->city());
            $manager->persist($recipe);
        }

        $manager->flush();
    }
}
