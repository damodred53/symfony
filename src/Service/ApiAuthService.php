<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiAuthService
{
    private HttpClientInterface $client;
    private ?string $token = null;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function login(string $username, string $password): ?string
    {
        $response = $this->client->request('POST', 'http://localhost/api/login', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $data = $response->toArray();
            $this->token = $data['token'] ?? null;
            return $this->token;
        }

        return null;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getAuthHeaders(): array
    {
        if (!$this->token) {
            return [];
        }

        return [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }
}
