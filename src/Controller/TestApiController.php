<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestApiController
{
    #[Route('/api/example', name: 'api_example', methods: ['GET'])]
    #[OA\Get(
        path: '/api/example',
        description: 'Retourne un exemple de réponse JSON.',
        summary: 'Example API Endpoint',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Exemple de réponse réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Ceci est une réponse exemple'),
                    ]
                )
            )
        ]
    )]
    public function example(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Ceci est une réponse exemple',
        ]);
    }
}
