<?php
// src/Security/ApiTokenAuthenticator.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\TokenDbRepository;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private $tokenRepo;

    public function __construct(TokenDbRepository $tokenRepo)
    {
        $this->tokenRepo = $tokenRepo;
    }

    public function supports(Request $request): ?bool
    {
        // Active pour les routes commençant par /api/service/
        return str_starts_with($request->getPathInfo(), '/api/service/');
    }

    public function authenticate(Request $request): \Symfony\Component\Security\Http\Authenticator\Passport\Passport
    {
        $apiToken = $request->headers->get('X-API-TOKEN');

        if (!$apiToken) {
            throw new AuthenticationException('No API token provided');
        }

        $tokenEntity = $this->tokenRepo->findOneBy(['token' => $apiToken]);

        if (!$tokenEntity) {
            throw new AuthenticationException('Invalid API token');
        }

        return new SelfValidatingPassport(
            new UserBadge('api-token-user', function() {
                // Return a dummy UserInterface object if needed
                return new class implements UserInterface {
                    public function getRoles(): array
                    { return ['ROLE_API']; }
                    public function getPassword() {}
                    public function getSalt() {}
                    public function getUsername(): string
                    { return 'api_token_user'; }
                    public function eraseCredentials(): void
                    {}
                    public function getUserIdentifier(): string
                    { return 'api_token_user'; }
                };
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        return null; // continue la requête
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage()
        ], 401);
    }
}
