<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;

class AppFixtures extends Fixture
{

    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($h = 1; $h <= 5; $h++) {
            $product = new Product();
            $product->setModelName($faker->word());
            $product->setPrice($faker->numberBetween(0, 100));
            $product->setColor($faker->safeColorName());
            $product->setOperatingSystem($faker->word());
            $product->setStock($faker->randomDigit());

            $manager->persist($product);
            $manager->flush();
        }
    }
}
