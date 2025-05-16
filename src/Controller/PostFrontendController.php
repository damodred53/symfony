<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class PostFrontendController extends AbstractController
{
    // Afficher la liste des posts
    #[Route('/api/post/frontend', name: 'app_post_frontend')]
    public function index(HttpClientInterface $client): Response
    {
        $response = $client->request('GET', 'http://localhost/api/jwt/posts');
        $posts = $response->toArray();

        return $this->render('post_frontend/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    // Créer un nouveau post
    #[Route('/api/post/frontend/create', name: 'app_post_frontend_create', methods: ['POST'])]
    public function create(HttpClientInterface $client, Request $request): Response
    {
        $content = $request->request->get('content');

        if ($content) {
            $response = $client->request('POST', 'http://localhost/api/jwt/posts', [
                'json' => [
                    'content' => $content,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                return $this->redirectToRoute('app_post_frontend');
            }

            $this->addFlash('error', 'Une erreur est survenue lors de la création du post.');
        }

        return $this->redirectToRoute('app_post_frontend');
    }

    // Supprimer un post
    #[Route('/api/post/frontend/delete/{id}', name: 'app_post_frontend_delete', methods: ['POST'])]
    public function delete(HttpClientInterface $client, int $id): Response
    {
        $response = $client->request('DELETE', 'http://localhost/api/jwt/posts/'.$id);

        if ($response->getStatusCode() === 204) {
            return $this->redirectToRoute('app_post_frontend');
        }

        $this->addFlash('error', 'Une erreur est survenue lors de la suppression du post.');

        return $this->redirectToRoute('app_post_frontend');
    }
}
