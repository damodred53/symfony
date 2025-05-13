<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TweetController extends AbstractController
{
    #[Route('/tweet', name: 'app_tweet')]
    public function index(): Response
    {
        return $this->render('home/tweet.html.twig', [
            'controller_name' => 'TweetController',
        ]);
    }
}
