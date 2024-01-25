<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $faker;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher
    ){
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $team = new Team;
        $team->setEmail('jnicolleau@gmail.com');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $team,
            'password'
        );
        $team->setPassword($hashedPassword);
        $team->setRoles(['ROLE_SUPER_ADMIN']);
        $team->setFirstname('Jessica');
        $team->setLastname('GARNAUD');
        $team->setUuid(md5(uniqid(mt_rand(), true)));
        $manager->persist($team);

        $manager->flush();

        for($i=1; $i<=3; $i++) {
            $user[$i] = new User;
            $user[$i]->setEmail('user' . $i . '@symfony.local');
            $user[$i]->setFirstname($this->faker->firstname());
            $user[$i]->setLastname($this->faker->lastname());
            $user[$i]->setUuid(md5(uniqid(mt_rand(), true)));
            $hashedPassword = $this->passwordHasher->hashPassword($user[$i], 'password');
            $user[$i]->setPassword($hashedPassword);
            $roles = ['ROLE_IDENTIFIED', 'ROLE_CUSTOMER', 'ROLE_ADMIN_CUSTOMER'];
            $role = array_rand(array_flip($roles), 1);
            $user[$i]->setRoles([$role]);
            $manager->persist($user[$i]);
        }

        $manager->flush();
    }
}
