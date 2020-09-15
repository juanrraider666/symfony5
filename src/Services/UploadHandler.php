<?php


namespace App\Services;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UploadHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function saveData(?string $gender, ?string $names)
    {

        $user = new User();

        $user->setGender($gender);
        $user->setNames($names);


        $this->manager->persist($user);


    }

}