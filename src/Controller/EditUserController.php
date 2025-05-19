<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[OA\Tag(name: "Users", description: "Operations about users")]
class EditUserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/api/jwt/user', name: 'app_user_update_me', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/user/me",
        summary: "Met à jour les informations de l'utilisateur connecté",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "username", type: "string", nullable: true),
                    new OA\Property(property: "email", type: "string", format: "email", nullable: true),
                    new OA\Property(property: "password", type: "string", nullable: true, format: "password"),
                    new OA\Property(property: "profilePicture", type: "string", nullable: true)
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: "Authorization",
                description: "JWT Token. Format: Bearer {token}",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur mis à jour",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "user", properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "username", type: "string"),
                            new OA\Property(property: "email", type: "string"),
                            new OA\Property(property: "profilePicture", type: "string", nullable: true),
                            new OA\Property(property: "createdAt", type: "string"),
                            new OA\Property(property: "updatedAt", type: "string", nullable: true)
                        ],
                            type: "object"
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Utilisateur non authentifié"),
            new OA\Response(response: 400, description: "Champs invalides")
        ]
    )]
    public function updateMe(
        Request                $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        if (array_key_exists('profilePicture', $data)) {
            $user->setProfilePicture($data['profilePicture']);
        }
        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'profilePicture' => $user->getProfilePicture(),
                'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
