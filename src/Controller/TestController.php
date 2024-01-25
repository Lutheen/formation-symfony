<?php

namespace App\Controller;

use App\Entity\Test;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{

    public function persist(EntityManagerInterface $entityManager, Test $test)
    {
        $entityManager->persist($test);
        $entityManager->flush();
        return true;
    }

    public function del(Test $test, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($test);
        $entityManager->flush();
        return true;
    }
}