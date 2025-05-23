<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/jwt/comments', name: 'api_comments_')]
#[OA\Tag(name: 'Comments')]
final class CommentController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne tous les commentaires.',
        summary: 'Liste des commentaires',
        security: [['bearer' => [], 'apiToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des commentaires réussie',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'postId', type: 'integer', example: 10),
                            new OA\Property(property: 'content', type: 'string', example: 'Super article !'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-15 13:00:00'),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function list(CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->findAll();
        $data = [];

        foreach ($comments as $comment) {
            $data[] = [
                'id' => $comment->getId(),
                'author' => $comment->getAuthor()?->getUsername(),
                'postId' => $comment->getPost()?->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }


    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        description: 'Créer un nouveau commentaire.',
        summary: 'Créer un commentaire',
        security: [['bearer' => [], 'apiToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'postId', type: 'integer', example: 10),
                    new OA\Property(property: 'content', type: 'string', example: 'Super post !'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Commentaire créé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Commentaire créé avec succès.'),
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, PostRepository $postRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? null;
        $postId = $data['postId'] ?? null;

        if (!$content || !$postId) {
            return $this->json(['error' => 'Post ID and Content are required'], 400);
        }

        $user = $userRepository->findOneBy([]);
        $post = $postRepository->find($postId);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setAuthor($user);
        $comment->setPost($post);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json([
            'message' => 'Commentaire créé avec succès.',
            'id' => $comment->getId(),
        ], 201);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'show', methods: ['GET'])]
    #[OA\Get(
        description: 'Voir un commentaire par son ID.',
        summary: 'Voir un commentaire',
        security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détail du commentaire',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                        new OA\Property(property: 'postId', type: 'integer', example: 10),
                        new OA\Property(property: 'content', type: 'string', example: 'Commentaire sympa !'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-15 13:00:00'),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function show(Comment $comment): JsonResponse
    {
        return $this->json([
            'id' => $comment->getId(),
            'author' => $comment->getAuthor()?->getUsername(),
            'postId' => $comment->getPost()?->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Modifier un commentaire existant.',
        summary: 'Modifier un commentaire',
        security: [['bearer' => [], 'apiToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Mise à jour du commentaire.'),
                ],
                type: 'object'
            )
        ),
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaire mis à jour',
            )
        ]
    )]
    public function update(Request $request, Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $comment->setContent($data['content']);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Commentaire mis à jour avec succès.',
        ]);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Supprimer un commentaire existant.',
        summary: 'Supprimer un commentaire',
        security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaire supprimé avec succès',
            )
        ]
    )]
    public function delete(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json([
            'message' => 'Commentaire supprimé avec succès.',
        ]);
    }
}
