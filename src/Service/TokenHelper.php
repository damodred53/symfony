<?php
namespace App\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenHelper
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    public function getDataToken(): ?array
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !is_object($token->getUser())) {
            return null;
        }

        $user = $token->getUser(); // <- ici se trouve l'utilisateur
        $jwt = $token->getCredentials();

        return [
            'jwt' => $jwt,
            'id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }

}
