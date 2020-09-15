<?php


namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LuckyController extends  AbstractController
{

    /**
     * @Route("/lucky/number")
     * @return Response
     * @throws \Exception
     */
    public function number()
    {
    $number = random_int(0,1000);
    return $this->render('lucky/number.html.twig',[
        'number' => $number
    ]);

    }

}