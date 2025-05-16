<?php

namespace App\Controller;
use App\Service\TokenHelper;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DatabaseTestController extends AbstractController
{
    #[Route('/api/jwt/test-db-connection', name: 'api_test_db_connection', methods: ['GET'])]
    #[OA\Get(
        path: '/api/test-db-connection',
        description: 'Returns a boolean indicating whether the database connection is successful.',
        summary: 'Test SQL connection',
        security: [['bearer' => []],['apiKey' => []]],
        tags: ['Database']
    )]
    #[OA\Response(
        response: 200,
        description: 'Result of DB connection test',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean')
            ],
            type: 'object'
        )
    )]
    public function testConnection(Connection $connection): JsonResponse
    {
        try {
            $connection->connect();
            $isConnected = $connection->isConnected();
            $error = null;
        } catch (\Throwable $e) {
            $isConnected = false;
            $error = $e->getMessage();
        }

        return $this->json([
            'success' => $isConnected,
            'error' => $error,
        ]);
    }

    #[Route('/api/jwt/GetDataToken', name: 'api_jwt_get_data_token', methods: ['GET'])]
    #[OA\Get(
        path: '/api/jwt/GetDataToken',
        description: 'Retrieve the JWT data token.',
        summary: 'Get JWT Token Data',
        security: ['bearer' => [],'apiKey' => []],
        tags: ['JWT']
    )]
    #[OA\Response(
        response: 200,
        description: 'JWT Token Data retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'jwt', type: 'string'),
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
            ],
            type: 'object'
        )
    )]
    public function getDataToken(TokenHelper $tokenHelper): JsonResponse
    {
        $data = $tokenHelper->getDataToken();

        if (!$data) {
            return $this->json(['error' => 'Token invalide ou utilisateur non connecté'], 401);
        }

        return $this->json([
            'username' => $data['username'],
            'email' => $data['email'],
            'roles' => $data['roles'],
        ]);
    }


}
