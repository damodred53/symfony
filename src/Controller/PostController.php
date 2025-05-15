<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/posts', name: 'api_posts_')]
#[OA\Tag(name: 'Posts')] // ➔ Groupe Swagger : Posts
final class PostController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Liste des posts',
        description: 'Retourne tous les posts publiés.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des posts réussie',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'content', type: 'string', example: 'Premier post de test.'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                        ]
                    )
                )
            )
        ]
    )]
    public function list(PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->findAll();

        $data = [];

        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'createdAt' => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
                'author' => $post->getAuthor()?->getUsername(),
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer un nouveau post',
        description: 'Crée un nouveau post pour un utilisateur.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Mon premier post.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Post créé avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post created successfully!'),
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                    ]
                )
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? null;

        if (!$content) {
            return $this->json(['error' => 'Content is required'], 400);
        }

        $user = $userRepository->findOneBy([]);

        if (!$user) {
            return $this->json(['error' => 'No user found to assign as author'], 400);
        }

        $post = new Post();
        $post->setContent($content);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setAuthor($user);

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post created successfully!',
            'id' => $post->getId(),
        ], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Voir un post spécifique',
        description: 'Retourne un post spécifique par son ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détail du post',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                        new OA\Property(property: 'content', type: 'string', example: 'Contenu du post.'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                    ]
                )
            )
        ]
    )]
    public function show(Post $post): JsonResponse
    {
        return $this->json([
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'createdAt' => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
            'author' => $post->getAuthor()?->getUsername(),
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Modifier un post',
        description: 'Modifie un post existant.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Nouveau contenu du post.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post modifié avec succès',
            )
        ]
    )]
    public function update(Request $request, Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }

        $post->setCreatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Post updated successfully!',
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Supprimer un post',
        description: 'Supprime un post existant.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post supprimé avec succès',
            )
        ]
    )]
    public function delete(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post deleted successfully!',
        ]);
    }
}
