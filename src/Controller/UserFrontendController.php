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
    public function index(): Response
    {
        $response = $this->client->request('GET', 'http://localhost/api/user');
        $users = $response->toArray();

        return $this->render('user_frontend/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/frontend/user/{id<\d+>}', name: 'app_user_frontend_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $response = $this->client->request('GET', "http://localhost/api/user/{$id}");
        $user = $response->toArray();

        return $this->render('user_frontend/show.html.twig', [
            'user' => $user,
        ]);
    }

     #[Route('/frontend/user/new', name: 'app_user_frontend_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupère les données du formulaire
            $data = [
                'username' => $request->request->get('username'),
                'email' => $request->request->get('email'),
                'password' => $request->request->get('password'),
            ];
            
            // Envoie les données sous forme de JSON à l'API backend
            try {
                $response = $this->client->request('POST', 'http://localhost/api/user/new', [
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
        // Récupération des données de l'utilisateur depuis l'API backend
        try {
            $response = $this->client->request('GET', "http://localhost/api/user/{$id}");
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
                $this->client->request('PUT', "http://localhost/api/user/{$id}/edit", [
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
    public function delete(int $id): Response
    {
        $this->client->request('DELETE', "http://localhost/api/user/{$id}");

        return $this->redirectToRoute('app_user_frontend_index');
    }
}
