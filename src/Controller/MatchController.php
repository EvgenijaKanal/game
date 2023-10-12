<?php

namespace App\Controller;

use App\Manager\MatchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     */
    public function index(MatchManager $matchManager): Response
    {
       return $this->render(
            'match\division.twig',
            $matchManager->generateMatches()
        );
    }
}
