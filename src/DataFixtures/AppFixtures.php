<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Post;

class AppFixtures extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadPosts($manager);

        // $product = new Product();
        // $manager->persist($product);
        // $manager->flush();
    }
    public function loadPosts(ObjectManager $manager)
    {
        for ($i = 1; $i < 20; $i++)
        {
            $post = new Post();
            $post->setTitle($this->faker->text(100));
            $post->setBody($this->faker->text(255));
            $manager->persist($post);
        }
        $manager->flush();
    }
}
