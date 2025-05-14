<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('tweets/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/tweets', name: 'app_tweets_list', methods: ['GET'])]
    public function list(): Response
    {
        $posts = [
            [
                'id' => 1,
                'author' => 'Alice',
                'content' => 'Premier post de test.',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'author' => 'Bob',
                'content' => 'Deuxième post de test.',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        ];

        return $this->render('home/tweet.html.twig', [
            'posts' => $posts,
        ]);
    }


    #[Route('/tweets', name: 'app_tweets_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {


        $content = $request->request->get('content');

       dd($content);

        $user = $userRepository->find(1);

        if (!$content || !$user) {
            $this->addFlash('error', 'Contenu manquant ou utilisateur fictif introuvable.');
            return $this->redirectToRoute('app_tweets_list');
        }

        try {
            $post = new Post();
            $post->setContent($content);
            $post->setAuthor($user);
            $post->setCreatedAt(new \DateTimeImmutable());

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post ajouté avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_tweets_list');
    }
}
