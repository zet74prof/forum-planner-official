<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Stand;
use App\Entity\Evaluation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EvalFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        $standRepository = $manager->getRepository(Stand::class);
        $stand = $standRepository->findAll();

        for ($i = 0; $i < 20; $i++) {
            $eval = new Evaluation();
            $eval->setNote($faker->numberBetween(0, 5));
            $randomStand = $stand[array_rand($stand)];
            $eval->setStand($randomStand);
            $eval->setCommentaire($faker->paragraph);

            $manager->persist($eval);
        }
        $manager->flush();
    }
}
