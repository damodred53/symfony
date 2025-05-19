<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PostFrontendController extends AbstractController
{

    #[Route('/post/frontend', name: 'app_post_frontend')]
    public function index(Request $request, HttpClientInterface $client): Response
    {
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;

        if (!$tokenApi) {
            throw new AccessDeniedHttpException('Token manquant.');
        }


        $response = $client->request('GET', $apiBaseUrl.'/api/jwt/posts', [
            'headers' => [
                'Accept' => 'application/json',
                'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
            ],
        ]);

        $posts = $response->toArray(false);

        return $this->render('post_frontend/index.html.twig', [
            'posts' => $posts,
        ]);
    }


    // Créer un nouveau post
    #[Route('/post/frontend/create', name: 'app_post_frontend_create', methods: ['POST'])]
    public function create(HttpClientInterface $client, Request $request): Response
    {
        $content = $request->request->get('content');
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;


        if ($content) {
            $response = $client->request('POST', $apiBaseUrl . '/api/jwt/posts', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenJwt,
                    'X-API-TOKEN' => $tokenApi,
                ],
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
    #[Route('/post/frontend/delete/{id}', name: 'app_post_frontend_delete', methods: ['POST'])]
    public function delete(HttpClientInterface $client, int $id, Request $request): Response
    {
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;
        $response = $client->request('DELETE', $apiBaseUrl . '/api/jwt/posts/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenJwt,
                'X-API-TOKEN' => $tokenApi,
            ],
        ]);

        if ($response->getStatusCode() === 204) {
            return $this->redirectToRoute('app_post_frontend');
        }

        $this->addFlash('error', 'Une erreur est survenue lors de la suppression du post.');

        return $this->redirectToRoute('app_post_frontend');
    }
}
