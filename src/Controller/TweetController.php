<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TweetController extends AbstractController
{
    #[Route('/tweet', name: 'app_tweet')]
    public function index(): Response
    {
        return new Response('Hello from TweetController!');
    }
}
