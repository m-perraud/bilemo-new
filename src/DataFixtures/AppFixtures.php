<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $clientPasswordHasher)
    {
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($h = 1; $h <= 10; $h++) {
            $product = new Product();
            $product->setModelName($faker->word())
                ->setPrice(strval($faker->numberBetween(0, 100)))
                ->setColor($faker->safeColorName())
                ->setOperatingSystem($faker->word())
                ->setStock($faker->randomDigit());

            $manager->persist($product);
            $manager->flush();

            for ($i = 1; $i <= 1; $i++) {
                $client = new Client();
                $client->setUsername($faker->word())
                    ->setRoles(["ROLE_USER"])
                    ->setPassword($this->clientPasswordHasher->hashPassword($client, 'AppFixturesPass'));

                $manager->persist($client);
                $manager->flush();

                for ($i = 1; $i <= 3; $i++) {
                    $user = new User();
                    $user->setUsername($faker->userName())
                        ->setFirstname($faker->firstName($gender = null))
                        ->setLastname($faker->lastName())
                        ->setEmail($faker->email())
                        ->setClient($client);
                    $manager->persist($user);
                    $manager->flush();
                }
            }
        }
    }
}
