<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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

        $errors = [];

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

            if ($response->getStatusCode() === 400) {
                $data = $response->toArray(false);
                $errors = $data['errors'] ?? ['global' => $data['error'] ?? 'Erreur inconnue.'];
            } else {
                $errors['global'] = 'Une erreur est survenue lors de la création du post.';
            }
        } else {
            $errors['content'] = 'Le contenu est requis.';
        }

        $postsResponse = $client->request('GET', 'http://localhost/api/jwt/posts');
        $posts = $postsResponse->toArray();

        return $this->render('post_frontend/index.html.twig', [
            'errors' => $errors,
            'posts' => $posts,
            'old_content' => $content
        ]);
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


    #[Route('/post/frontend/search', name: 'app_post_frontend_search', methods: ['GET'])]
    public function search(HttpClientInterface $client, Request $request): Response
    {
        $query = $request->query->get('query');

        $posts = [];

        if ($query) {
            $response = $client->request('GET', 'http://localhost/api/jwt/posts/search', [
                'query' => ['keyword' => $query],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->jwtToken,
                ]
            ]);

            $posts = $response->toArray();
        }

        return $this->render('post_frontend/search.html.twig', [
            'posts' => $posts,
            'query' => $query
        ]);
    }

    #[Route('/post/{postId}/like', name: 'post_like_api_proxy', methods: ['POST'])]
    public function likeProxy(
        int $postId,
        HttpClientInterface $client,
        Request $request
    ): Response {
        $response = $client->request('POST', "http://localhost/api/jwt/likes/{$postId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtToken,
            ]
        ]);

        // Rediriger avec l'éventuel mot-clé de recherche
        $query = $request->query->get('query');

        return $this->redirectToRoute('app_post_frontend_search', $query ? ['query' => $query] : []);
    }

    #[Route('/post/frontend/{id}/show', name: 'app_post_frontend_show', methods: ['GET'])]
    public function show(HttpClientInterface $client, int $id): Response
    {
        $response = $client->request('GET', "http://localhost/api/jwt/posts/{$id}/with-comments");

        if ($response->getStatusCode() !== 200) {
            throw $this->createNotFoundException("Post non trouvé.");
        }

        $data = $response->toArray();

        return $this->render('post_frontend/show.html.twig', [
            'post' => $data['post'],
            'comments' => $data['comments']
        ]);
    }

    #[Route('/post/frontend/{id}/comment', name: 'app_post_frontend_add_comment', methods: ['POST'])]
    public function addComment(
        HttpClientInterface $client,
        Request $request,
        int $id
    ): Response {
        $content = $request->request->get('content');
        $errors = [];

        if ($content) {
            $response = $client->request('POST', 'http://localhost/api/jwt/comments', [
                'json' => [
                    'postId' => $id,
                    'content' => $content
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->jwtToken,
                ]
            ]);

            if ($response->getStatusCode() === 201) {
                return $this->redirectToRoute('app_post_frontend_show', ['id' => $id]);
            } else {
                $errors['global'] = 'Erreur lors de la création du commentaire.';
            }
        } else {
            $errors['content'] = 'Le contenu du commentaire est requis.';
        }

    }

    #[Route('/posts/frontend', name: 'app_post_frontend', methods: ['GET'])]
public function listWithCommentsAndLikes(HttpClientInterface $client): Response
{
    // Effectuer la requête à l'API backend
    $response = $client->request('GET', 'http://localhost/api/jwt/posts/with-comments-and-likes', [
        'headers' => [
            'Authorization' => 'Bearer ' . $this->jwtToken,
        ],
    ]);

    // Vérifier si la réponse est correcte (code 200)
    if ($response->getStatusCode() !== 200) {
        // Récupérer le contenu de l'erreur (si disponible)
        $errorData = json_decode($response->getContent(), true);
        $errorMessage = $errorData['error'] ?? 'Erreur inconnue';

        // Ajouter un message flash d'erreur
        $this->addFlash('error', 'Impossible de récupérer les posts. Détails de l\'erreur : ' . $errorMessage);

        // Rediriger vers la page de la liste des posts
        return $this->redirectToRoute('app_post_frontend');
    }

    // Si la réponse est correcte, récupérer les posts
    $posts = $response->toArray();

    // Afficher la page des posts
    return $this->render('post_frontend/index.html.twig', [
        'posts' => $posts,
    ]);
}


}
