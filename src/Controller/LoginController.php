<?php

namespace App\Controller;
namespace App\Controller;
use App\Service\TokenHelper;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class LoginController
{

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    #[OA\Post(
        description: 'Cette route permet aux utilisateurs de se connecter via json_login.',
        summary: 'Authentification utilisateur',
        security: [['apiToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cette route est gérée par json_login.'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function login(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Cette route est gérée par json_login.',
        ]);
    }
}
