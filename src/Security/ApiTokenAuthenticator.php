<?php
// src/Security/ApiTokenAuthenticator.php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\TokenDbRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services;
use function Webmozart\Assert\Tests\StaticAnalysis\null;
use function Webmozart\Assert\Tests\StaticAnalysis\string;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private TokenDbRepository $tokenRepo;
    private JWSProviderInterface  $jwtManager;

    public function __construct(TokenDbRepository $tokenRepo, JWSProviderInterface  $jwtManager)
    {
        $this->tokenRepo = $tokenRepo;
        $this->jwtManager = $jwtManager;
    }
    public function supports(Request $request): ?bool
    {
        $path = $request->getPathInfo();


        return str_starts_with($path, '/api') && $path !== '/api/doc';
    }

    public function authenticate(Request $request): \Symfony\Component\Security\Http\Authenticator\Passport\Passport
    {
        $apiToken = $request->headers->get('X-API-TOKEN');
        $apiBearer = $request->headers->get('Authorization');

        if (!$apiToken) {
            throw new AuthenticationException('No API token provided to access this resource : ' . $request->getPathInfo());
        }

        $tokenEntity = $this->tokenRepo->findOneBy(['token' => $apiToken]);

        if (!$tokenEntity) {
            throw new AuthenticationException('Invalid API token');
        }

        $roles = ['ROLE_API'];

        if ($apiBearer && str_starts_with($apiBearer, 'Bearer ')) {
            $token = substr($apiBearer, 7);

            try {
                $jws = $this->jwtManager->load($token);

                $payload = $jws->getPayload();


                if (isset($payload['exp']) && time() >= $payload['exp']) {
                    throw new AuthenticationException('JWT token expired');
                }

                $roles[] = 'ROLE_API_AND_JWT';

            } catch (\Throwable $e) {
                throw new AuthenticationException('JWT processing failed: ' . $e->getMessage());
            }
        }


        return new SelfValidatingPassport(
            new UserBadge('api-token-user', function () use ($roles) {
                return new class($roles) implements UserInterface {
                    private array $roles;



                    public function __construct(array $roles)
                    {
                        $this->roles = $roles;
                    }

                    public function getRoles(): array
                    {
                        return $this->roles;
                    }

                    public function getPassword() {}
                    public function getSalt() {}
                    public function getUsername(): string { return 'api_token_user'; }
                    public function eraseCredentials(): void {}
                    public function getUserIdentifier(): string { return 'api_token_user'; }
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
