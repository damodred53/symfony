<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\PostRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;

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
                    'Authorization' => 'Bearer VOTRE_TOKEN_ICI'
                ]
            ]);

            $posts = $response->toArray();
        }

        return $this->render('post_frontend/search.html.twig', [
            'posts' => $posts,
            'query' => $query
        ]);
    }

    #[Route('/post/{postId}/like-toggle', name: 'post_like_toggle', methods: ['POST'])]
    public function toggleLike(
        int $postId,
        PostRepository $postRepository,
        LikeRepository $likeRepository,
        EntityManagerInterface $entityManager,
        Request $request // <- important pour récupérer le paramètre 'query'
    ): Response {
        $user = $this->getUser();
        $post = $postRepository->find($postId);

        if (!$post || !$user) {
            throw $this->createNotFoundException('Post non trouvé ou utilisateur non connecté');
        }

        // Vérifie si l'utilisateur a déjà liké
        $like = $likeRepository->findOneBy([
            'post' => $post,
            'author' => $user
        ]);

        if ($like) {
            $entityManager->remove($like);
        } else {
            $newLike = new \App\Entity\Like();
            $newLike->setAuthor($user);
            $newLike->setPost($post);
            $newLike->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($newLike);
        }

        $entityManager->flush();

        // Redirection vers la page de recherche avec le terme (s'il existe)
        $query = $request->query->get('query');

        return $this->redirectToRoute('app_post_frontend_search', $query ? ['query' => $query] : []);
    }
}
