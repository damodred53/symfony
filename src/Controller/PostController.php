<?php

namespace App\Controller;

use App\Dto\Posts\PostCreateDTO;
use App\Dto\Posts\PostDTO;
use App\Dto\Posts\PostFullDTO;
use App\Dto\Posts\PostUpdateDTO;
use App\Dto\Posts\PostWithCommentsDTO;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/jwt/posts', name: 'api_posts_')]
#[OA\Tag(name: 'Posts')]

// ➔ Groupe Swagger : Posts
final class PostController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne tous les posts publiés.',
        summary: 'Liste des posts',
        security: [['bearer' => [], 'apiToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des posts réussie',

                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'content', type: 'string', example: 'Premier post de test.'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function list(PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->findAll();

        $data = array_map(fn(Post $post) => (new PostDTO($post))->toArray(), $posts);

        return $this->json($data);
    }


    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouveau post pour un utilisateur.',
        summary: 'Créer un nouveau post',
        security: [['bearer' => [], 'apiToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Mon premier post.'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Post créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post created successfully!'),
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {





        $data = json_decode($request->getContent(), true);
        $dto = PostCreateDTO::fromArray($data);

        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 400);
        }

        $user = $userRepository->findOneBy([]);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 400);
        }

        $post = new Post();
        $post->setContent($dto->content);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setAuthor($user);

        $em->persist($post);
        $em->flush();

        return $this->json([
            'message' => 'Post created',
            'post' => (new PostDTO($post))->toArray()
        ], 201);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'show', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne un post spécifique par son ID.',
        summary: 'Voir un post spécifique',
        security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détail du post',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                        new OA\Property(property: 'content', type: 'string', example: 'Contenu du post.'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function show(Post $post): JsonResponse
    {
        return $this->json((new PostDTO($post))->toArray());
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Modifie un post existant.',
        summary: 'Modifier un post',
          security: [['bearer' => [], 'apiToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Nouveau contenu du post.')
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
                description: 'Post modifié avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post updated successfully!'),
                        new OA\Property(
                            property: 'post',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'content', type: 'string', example: 'Contenu modifié du post'),
                                new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                                new OA\Property(property: 'likeCount', type: 'integer', example: 3),
                            ]
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Erreur de validation ou JSON invalide'
            )
        ]
    )]
    public function update(Request $request, Post $post, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $dto = PostUpdateDTO::fromArray($data);

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $messages = [];
                foreach ($errors as $error) {
                    $messages[$error->getPropertyPath()] = $error->getMessage();
                }

                return $this->json(['errors' => $messages], 400);
            }

            $post->setContent($dto->content);
            $post->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'message' => 'Post updated successfully!',
                'post' => (new PostDTO($post))->toArray(),
            ]);
        } catch (\JsonException $e) {
            return $this->json(['error' => 'Invalid JSON body'], 400);
        }
    }

    #[Route('/{id}', requirements: ['id' => '\d+'],  name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Supprime un post existant.',
        summary: 'Supprimer un post',
          security: [['bearer' => [], 'apiToken' => []]],
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

    #[Route('/search', name: 'search', methods: ['GET'])]
    #[OA\Get(
        description: 'Recherche de posts par mot-clé.',
        summary: 'Rechercher des posts',
          security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Résultats de la recherche',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'author', type: 'string', example: 'Alice'),
                            new OA\Property(property: 'content', type: 'string', example: 'Contenu du post.'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-14 12:00:00'),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function search(Request $request, PostRepository $postRepository): JsonResponse
    {
        $keyword = $request->query->get('keyword');

        if (!$keyword) {
            return $this->json(['error' => 'Keyword is required'], 400);
        }

        $posts = $postRepository->createQueryBuilder('p')
            ->where('LOWER(p.content) LIKE LOWER(:keyword)')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();

        $user = $this->getUser();
        $data = array_map(fn(Post $post) => (new PostFullDTO($post, $user))->toArray(), $posts);

        return $this->json($data);
    }

    #[Route('/with-comments-and-likes', name: 'list_full', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne tous les posts avec commentaires et likes',
        summary: 'Liste complète des posts',
          security: [['bearer' => [], 'apiToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste complète des posts',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'content', type: 'string'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'author', type: 'string'),
                            new OA\Property(property: 'likeCount', type: 'integer'),
                            new OA\Property(property: 'comments', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'author', type: 'string'),
                                    new OA\Property(property: 'content', type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string'),
                                ],
                                type: 'object'
                            )),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function listWithCommentsAndLikes(PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->findAll();

        $user = $this->getUser();
        $data = array_map(fn(Post $post) => (new PostFullDTO($post, $user))->toArray(), $posts);

        return $this->json($data);
    }

    #[Route('/{id}/with-comments', name: 'show_with_comments', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne un post avec tous ses commentaires associés.',
        summary: 'Voir un post + ses commentaires',
          security: [['bearer' => [], 'apiToken' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post + commentaires récupérés',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'post', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'content', type: 'string', example: 'Mon premier post.'),
                            new OA\Property(property: 'author', type: 'string', example: 'test'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-15 12:26:16'),
                        ], type: 'object'),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'author', type: 'string'),
                                new OA\Property(property: 'content', type: 'string'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        ))
                    ]
                )
            )
        ]
    )]
    public function showWithComments(Post $post): JsonResponse
    {
        return $this->json((new PostWithCommentsDTO($post))->toArray());
    }

    #[Route('/by-user/{id}', name: 'by_user', methods: ['GET'])]
    #[OA\Get(
        description: 'Retourne tous les posts d’un utilisateur spécifique avec commentaires et likes.',
        summary: 'Posts d’un utilisateur (avec commentaires et likes)',
        security: [['bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des posts de l’utilisateur',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'content', type: 'string'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'author', type: 'string'),
                            new OA\Property(property: 'likeCount', type: 'integer'),
                            new OA\Property(property: 'isLikedByCurrentUser', type: 'boolean'),
                            new OA\Property(property: 'comments', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'author', type: 'string'),
                                    new OA\Property(property: 'content', type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string'),
                                ],
                                type: 'object'
                            )),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    public function postsByUser(
        int $id,
        UserRepository $userRepository,
        PostRepository $postRepository
    ): JsonResponse {
        $targetUser = $userRepository->find($id);

        if (!$targetUser) {
            return $this->json(['error' => 'Utilisateur non trouvé.'], 404);
        }

        $posts = $postRepository->findBy(['author' => $targetUser], ['createdAt' => 'DESC']);
        $currentUser = $this->getUser();

        $data = array_map(fn(Post $post) => (new PostFullDTO($post, $currentUser))->toArray(), $posts);

        return $this->json($data);
    }
}
