<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Ingredient;
use Faker\Factory;
use Faker\Generator;
use App\Entity\Recipe;
use App\Entity\Mark;
use App\Entity\User;
use App\Entity\Contact;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');

    }
    public function load(ObjectManager $manager): void
    {
        //Users
        $users= [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) == 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');
            $users[] = $user;
            $manager->persist($user);
        }
        //Ingredients
        $ingredients = [];
        for ($i = 1; $i <= 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word);
            $ingredient->setPrice(mt_rand(0, 100));
            $ingredient->setUser($users[mt_rand(0, count($users) - 1)]);
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }
        //Recipes
        $recipes = [];
        for ($j = 1; $j <= 25; $j++) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->word);
            $recipe->setPrice(mt_rand(0, 1) == 1 ? mt_rand(0, 1000) : null);
            $recipe->setTime(mt_rand(0, 1) == 1 ? mt_rand(1,1440) : null);
            $recipe->setNbPeople(mt_rand(0, 1) == 1 ? mt_rand(1,50) : null);
            $recipe->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1,5) : null);
            $recipe->setDescription($this->faker->text);
            $recipe->setIsFavorite(mt_rand(0, 1) == 1 ? true : false);
            $recipe->setIsPublic(mt_rand(0, 1) == 1 ? true : false);
            $recipe->setUser($users[mt_rand(0, count($users) - 1)]);
            for ($k = 0; $k < mt_rand(5, 15); $k++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $recipes[] = $recipe;
            $manager->persist($recipe);
        }
        //Marks
        foreach ($recipes as $recipe) {
            for ($i=0; $i < mt_rand(0,4) ; $i++) { 
                $mark = new Mark();
                $mark->setMark(mt_rand(1,5))
                    ->setUser($users[mt_rand(0, count($users) - 1)])
                    ->setRecipe($recipe);
                $manager->persist($mark);

            }
        }
        //Contacts
        for ($i=0; $i < 5 ; $i++) { 
            $contact = new Contact();
            $contact->setFullName($this->faker->name())
                ->setEmail($this->faker->email())
                ->setSubject('Demande n°'. ($i+1))
                ->setMessage($this->faker->text);
            
            $manager->persist($contact);
        }
        $manager->flush();
    }
}
