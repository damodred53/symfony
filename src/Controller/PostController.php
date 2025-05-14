<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/posts', name: 'api_posts_')]
final class PostController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
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
    public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? null;

        if (!$content) {
            return $this->json(['error' => 'Content is required'], 400);
        }

        $user = $userRepository->findOneBy([]); // à remplacer par getUser() plus tard

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
    public function update(Request $request, Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }

        $post->setCreatedAt(new \DateTimeImmutable()); // Tu peux aussi faire updatedAt si tu rajoutes le champ

        $entityManager->flush();

        return $this->json([
            'message' => 'Post updated successfully!',
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post deleted successfully!',
        ]);
    }
}
