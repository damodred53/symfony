<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentFrontendController extends AbstractController
{
    #[Route('/comment/api-proxy', name: 'app_comment_api_front', methods: ['POST'])]
    public function proxyCommentToApi(
        Request $request,
        HttpClientInterface $httpClient
    ): RedirectResponse {
        $postId = $request->request->get('post_id');
        $content = $request->request->get('content');

        if (!$postId || !$content) {
            $this->addFlash('error', 'Post ID et contenu requis.');
           return $this->redirectToRoute('app_post_frontend');
        }

        // dd($content, $postId);

        $tokenJwt = $request->getSession()->get('jwt_token');
        $tokenApi = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;
        $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? 'http://localhost';

        try {
            $response = $httpClient->request('POST', $apiBaseUrl . '/api/jwt/comments', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenJwt,
                    'X-API-TOKEN' => $tokenApi,

                ],
                'json' => [
                    'postId' => (int)$postId,
                    'content' => $content,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Commentaire ajouté via l’API.');
            } else {
                $this->addFlash('error', 'Erreur API : ' . $response->getContent(false));
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de communication avec l’API : ' . $e->getMessage());
        }

       return $this->redirectToRoute('app_post_frontend');
    }
}
