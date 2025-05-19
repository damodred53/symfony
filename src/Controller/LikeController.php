<?php

namespace App\Controller;

use App\DTO\LikeDTO;
use App\Entity\Like;
use App\Entity\Post;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/jwt/likes', name: 'api_likes_')]
#[OA\Tag(name: 'Likes')]
final class LikeController extends AbstractController
{
    #[Route('/{postId}', name: 'like', methods: ['POST'])]
    #[OA\Post(
        description: 'Like un post spécifique.',
        summary: 'Liker un post',
         security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(
                name: 'postId',
                description: 'ID du post à liker',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Post liké',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'author', type: 'string'),
                        new OA\Property(property: 'postId', type: 'integer'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Post introuvable ou déjà liké')
        ]
    )]
    public function like(
        int $postId,
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        UserRepository $userRepository,
        LikeRepository $likeRepository
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $post = $postRepository->find($postId);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $existingLike = $likeRepository->findOneBy(['author' => $user, 'post' => $post]);
        if ($existingLike) {
            return $this->json(['message' => 'Post already liked'], 200);
        }

        $like = new Like();
        $like->setAuthor($user);
        $like->setPost($post);
        $like->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($like);
        $entityManager->flush();

        $likeDto = new LikeDTO($like);

        return $this->json($likeDto->toArray(), 201);
    }

    #[Route('/{postId}', name: 'unlike', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Unlike un post spécifique.',
        summary: 'Retirer un like sur un post',
         security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(
                name: 'postId',
                description: 'ID du post à unliker',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Post unliké'),
            new OA\Response(response: 404, description: 'Like non trouvé')
        ]
    )]
    public function unlike(
        int $postId,
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        UserRepository $userRepository,
        LikeRepository $likeRepository
    ): JsonResponse {
        $user = $userRepository->findOneBy([]);
        $post = $postRepository->find($postId);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $like = $likeRepository->findOneBy(['author' => $user, 'post' => $post]);

        if (!$like) {
            return $this->json(['error' => 'Like not found'], 404);
        }

        $entityManager->remove($like);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post unliked successfully!',
            'postId' => $postId
        ]);
    }
}
