<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: "Users", description: "Operations about users")]
class EditUserController extends AbstractController
{
    #[Route('/api/jwt/edit', name: 'api_jwt_edit', methods: ['PUT'])]
    #[OA\Put(
        description: "Permet de modifier les informations de l'utilisateur identifié par le JWT transmis dans l'en-tête Authorization.",
        summary: "Modifier un utilisateur authentifié par JWT",
        security: ['bearer' => [],'apiKey' => []],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "email", type: "string", example: "nouvel.email@example.com"),
                    new OA\Property(property: "profilePicture", type: "string", example: "https://exemple.com/ma-photo.png"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur modifié",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "username", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "profilePicture", type: "string"),
                        new OA\Property(property: "updatedAt", type: "string", format: "date-time"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Token manquant ou invalide"
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur introuvable"
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides"
            )
        ]
    )]
    public function editUserWithJwt(
        Request $request,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {

        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return $this->json(['error' => 'Token manquant'], 401);
        }

        $jwt = substr($authorizationHeader, 7);

        try {
            $payload = $jwtManager->parse($jwt);
            $username = $payload['username'] ?? null;
        } catch (\Exception $e) {
            return $this->json(['error' => 'Token invalide'], 401);
        }

        if (!$username) {
            return $this->json(['error' => 'Champ username non trouvé dans le token'], 400);
        }


        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => $username]);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Modifier les champs demandés (par exemple email et profilePicture)
        $content = json_decode($request->getContent(), true) ?? [];

        if (isset($content['email'])) {
            $user->setEmail($content['email']);
        }
        if (isset($content['profilePicture'])) {
            $user->setProfilePicture($content['profilePicture']);
        }

        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'profilePicture' => $user->getProfilePicture(),
            'updatedAt' => $user->getUpdatedAt()?->format('c'),
        ]);
    }
}
