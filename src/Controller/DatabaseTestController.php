<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DatabaseTestController extends AbstractController
{
    #[Route('/api/test-db-connection', name: 'api_test_db_connection', methods: ['GET'])]
    #[OA\Get(
        path: '/api/test-db-connection',
        description: 'Returns a boolean indicating whether the database connection is successful.',
        summary: 'Test SQL connection',
        security: [['bearer' => []]],
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

}
