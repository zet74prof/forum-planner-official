<?php

namespace App\DataFixtures;

use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GroupeFixtures extends Fixture
{
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $team = new Team();
            $team->setOwner($this->getReference('user_' .$faker->numberBetween(0, 9)));
            $team->setName($faker->unique()->company);
            $manager->persist($team);
        }
        
        $manager->flush();
    }
}
