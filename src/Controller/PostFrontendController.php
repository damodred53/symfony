<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class PostFrontendController extends AbstractController
{
    private string $jwtToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDc0MDQ3OTksImV4cCI6MTc0NzQwODM5OSwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6InRlc3QifQ.HiKOK3FQhn8JnLifczC1RnNyWgP_dyRsjVV_EJ_YHOiS4Xir9MyRb6mkeCLadSupoS1tu3yCHh1cdXJAmEtfEzD4VCpJ9QO3o67r34x77-5ynepXmmYW0_zCv3zDULNGR6CTAEYLI7crWCXBdIAMWR-pw04xLp10jEBWQoMU-IzMZszEEe4rrdCoFAXlBIFsb7dKfdqARCnlZoOagD7knRZZT817U1SfCuPvAi-KFmaOTSjxqlRE10TajUbYiMN9g0HOjkSvs-f_piMlWTiCA1bubg_Q7KECR0JYK-dLzZvelJx91jUF81jSHnnvXL-hbDCiSY5N37Ci5KBwKQvDt-QiO1DnkUh8zEkJpL5sw_9L__r0CBFZIUOEKKOSVloZAiIrsd5lqu2XV-HjwNeb-1SJXDGlVlhC4_APFvrZdahDXZPD6oamyr8xFGhNwbeVsjqA_9bBxBddgVZKHIWP7T3t2bZSxVkZq1JXDqGGcX4QzNRJ9wOrLdv-EwRNa-JWcvSeIa0CdQNcbA0sJjoZPB82hRjwGct9hRmbEfWRQJ20xp_uD6kNkPUz-SB5PSPb6Tqoe9vHrJ2M8DVSEyxNLzoum4P7MZpRS3rDVvAB_CeMzh-VwSs92nmjhxePoJyhvbkFyXEz-4puegn-kpYXG4QODDVVYDhiXRtf57OvsAs';

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

    #[Route('/api/post/frontend/create', name: 'app_post_frontend_create', methods: ['POST'])]
    public function create(HttpClientInterface $client, Request $request): Response
    {
        $content = $request->request->get('content');
        $errors = [];

        // On tente la création
        if ($content) {
            $response = $client->request('POST', 'http://localhost/api/jwt/posts', [
                'json' => ['content' => $content],
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

    // Rechercher un post
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
}
