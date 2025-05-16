<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/user')]
#[OA\Tag(name: 'User')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user',
        summary: 'List all users',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns all users',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'username', type: 'string'),
                            new OA\Property(property: 'email', type: 'string'),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $data = array_map(fn(User $user) => [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ], $users);

        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_user_new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/new',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'id', type: 'integer'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Missing fields')
        ]
    )]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$email || !$password) {
            return new JsonResponse(['error' => 'Missing fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'User created successfully',
            'id' => $user->getId(),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', name: 'app_user_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/{id}',
        summary: 'Show user details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'username', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'profilePicture', type: 'string', nullable: true),
                        new OA\Property(property: 'createdAt', type: 'string'),
                        new OA\Property(property: 'updatedAt', type: 'string', nullable: true),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function show(User $user): JsonResponse
    {
        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'profilePicture' => $user->getProfilePicture(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[OA\Get(
        path: '/api/user/{id}/edit',
        summary: 'Edit user form (HTML)',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Render edit form (HTML)')
        ]
    )]
    #[OA\Post(
        path: '/api/user/{id}/edit',
        summary: 'Submit user edit form (HTML)',
        requestBody: new OA\RequestBody(
            description: 'Form data (HTML form)',
            required: false
        ),
        responses: [
            new OA\Response(response: 303, description: 'Redirect to user index')
        ]
    )]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/{id}',
        summary: 'Delete a user',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: '_token', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 303, description: 'Redirect to user index'),
            new OA\Response(response: 403, description: 'Invalid CSRF token'),
        ]
    )]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
