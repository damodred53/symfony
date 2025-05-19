<?php

namespace App\Controller;
use App\Entity\TokenDb;
use App\Service\TokenHelper;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;


final class TokenGestionController extends AbstractController
{
    #[Route('/api/jwt/token/gestion', name: 'app_token_gestion', methods: ['POST'])]

    public function index(): Response
    {
        return $this->render('token_gestion/index.html.twig', [
            'controller_name' => 'TokenGestionController',
        ]);
    }
    #[Route('/api/jwt/token/add', name: 'app_token_add', methods: ['POST'])]


    #[OA\Post(
        path: '/api/jwt/token/add',
        description: 'Adds a new API token to the database. Requires authentication and "ROLE_ADMIN".',
        summary: 'Add a new API token',
        security: [['bearer' => [], 'apiToken' => []]],
        requestBody: new OA\RequestBody(
            description: 'JSON object containing token and service name.',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token', description: 'The token to be added.', type: 'string'),
                    new OA\Property(property: 'service_name', description: 'The name of the service that will use this token.', type: 'string')
                ],
                type: 'object'
            )
        ),
        tags: ['Token Management']
        )]
        #[OA\Response(
            response: 201,
            description: 'Token successfully added.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', description: 'Indicates if the operation was successful.', type: 'boolean'),
                    new OA\Property(property: 'message', description: 'A message providing additional information.', type: 'string')
                ],
                type: 'object'
            )
        )]
        #[OA\Response(
            response: 400,
            description: 'Bad request. Invalid input data.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', description: 'Indicates that the operation failed.', type: 'boolean'),
                    new OA\Property(property: 'message', description: 'Error message detailing what went wrong.', type: 'string')
                ],
                type: 'object'
            )
        )]
        #[OA\Response(
            response: 401,
            description: 'Unauthorized. Authentication is required.',
        )]
        #[OA\Response(
            response: 403,
            description: 'Forbidden. You do not have the required role (ROLE_ADMIN).',
        )]
        #[OA\Response(
            response: 500,
            description: 'Internal server error. Something went wrong on the server side.',
        )]
    public function createToken(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token'], $data['service_name'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid input data!'
            ], Response::HTTP_BAD_REQUEST);
        }


        $token = new TokenDb();
        $token->setToken($data['token'])
            ->setServiceName($data['service_name']);

        $entityManager->persist($token);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Token successfully added!'
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/service/JeSuisUnTokenApi', name: 'api_token_check', methods: ['GET'])]
    #[OA\Get(
        path: '/api/service/JeSuisUnTokenApi',
        description: 'Retourne true si un token API valide est fourni dans l’en-tête X-API-TOKEN',
        summary: 'Vérifie si un token API est valide',
        security: [['apiToken' => []]],

        tags: ['Token Management'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token API valide',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token API manquant ou invalide')
        ]
    )]
    public function checkApiToken(): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }


}
