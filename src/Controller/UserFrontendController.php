<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class UserFrontendController extends AbstractController
{
    public function __construct(private readonly HttpClientInterface $client) {}

    #[Route('/frontend/user', name: 'app_user_frontend_index', methods: ['GET'])]
    public function index( Request $request): Response
    {
        $content = $request->request->get('content');
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;
        $response = $this->client->request('GET', $apiBaseUrl . '/api/jwt/user', [
            'headers' => [
                'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
            ]
        ]);
        $users = $response->toArray();

        return $this->render('user_frontend/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/frontend/user/{id<\d+>}', name: 'app_user_frontend_show', methods: ['GET'])]
    public function show(int $id, Request $request): Response
    {
        $content = $request->request->get('content');
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;
        $response = $this->client->request('GET', $apiBaseUrl . "/api/jwt/user/{$id}", [
            'headers' => [
                'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
            ]
        ]);
        $user = $response->toArray();

        return $this->render('user_frontend/show.html.twig', [
            'user' => $user,
        ]);
    }

     #[Route('/frontend/user/new', name: 'app_user_frontend_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {

        $content = $request->request->get('content');
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;

        if ($request->isMethod('POST')) {
            // Récupère les données du formulaire
            $data = [
                'username' => $request->request->get('username'),
                'email' => $request->request->get('email'),
                'password' => $request->request->get('password'),
            ];

            // Envoie les données sous forme de JSON à l'API backend
            try {
                $response = $this->client->request('POST', $apiBaseUrl . '/api/jwt/user/new', [
                    'headers' => [
                        'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
                    ],
                    'json' => $data,
                ]);

                // Vérifie la réponse de l'API backend
                $statusCode = $response->getStatusCode();
                $content = $response->toArray();

                if ($statusCode === 201) {
                    // Si tout est bon (utilisateur créé), redirige vers la liste des utilisateurs
                    $this->addFlash('success', 'User created successfully!');
                    return $this->redirectToRoute('app_user_frontend_index');
                } else {
                    // Si l'API renvoie une erreur, on affiche un message
                    $this->addFlash('error', $content['error'] ?? 'An error occurred.');
                }
            } catch (\Exception $e) {
                // Si une exception se produit (API inaccessible, etc.)
                $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            }
        }

        return $this->render('user_frontend/new.html.twig');
    }

    #[Route('/frontend/user/{id}/edit', name: 'app_user_frontend_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $content = $request->request->get('content');
        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;
        // Récupération des données de l'utilisateur depuis l'API backend
        try {
            $response = $this->client->request('GET', $apiBaseUrl . "/api/jwt/user/{$id}", [
                'headers' => [
                    'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
                ]
            ]);
            $userData = $response->toArray();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to fetch user data: ' . $e->getMessage());
            return $this->redirectToRoute('app_user_frontend_index');
        }

        if ($request->isMethod('POST')) {
            $data = [
                'username' => $request->request->get('username'),
                'email' => $request->request->get('email'),
            ];

            // Envoi de la mise à jour au backend
            try {
                $this->client->request('PUT', $apiBaseUrl . "/api/jwt/user/{$id}/edit", [
                    'headers' => [
                        'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
                    ],
                    'json' => $data,
                ]);

                $this->addFlash('success', 'User updated successfully!');

                return $this->redirectToRoute('app_user_frontend_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Update failed: ' . $e->getMessage());
            }
        }

        return $this->render('user_frontend/edit.html.twig', [
            'user' => $userData
        ]);
    }

    #[Route('/frontend/user/{id}/delete', name: 'app_user_frontend_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $tokenJwt = $request->getSession()->get('jwt_token');
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;

        $this->client->request('DELETE', $apiBaseUrl . "/api/jwt/user/{$id}", [
            'headers' => [
                'X-API-TOKEN' => $tokenApi,
                'Authorization' => 'Bearer ' . $tokenJwt,
            ]
        ]);

        return $this->redirectToRoute('app_user_frontend_index');
    }
}
