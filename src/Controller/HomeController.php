<?php

namespace App\Controller;
use App\Form\LoginType;
use App\Model\LoginData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(Request $request): Response
    {
        $loginData = new LoginData();
        $form = $this->createForm(LoginType::class, $loginData);

        $form->handleRequest($request);

        $message = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $_ENV['BACKEND_AUTH_TOKEN'] ?? $_SERVER['BACKEND_AUTH_TOKEN'] ?? null;

            $json = json_encode($loginData);
              $client = HttpClient::create();
              $apiBaseUrl = $_ENV['API_URL'] ?? $_SERVER['API_URL'] ?? null;

              $url = $apiBaseUrl . "/api/login";
            try {
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-TOKEN' => $token
                    ],
                    'body' => $json,
                ]);


                $data = $response->toArray(false);
                dump($data);

            } catch (TransportExceptionInterface $e) {
                dump("Erreur réseau : " . $e->getMessage());
            } catch (ClientExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
                dump("Erreur HTTP : " . $e->getMessage());
            }
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'message' => $message
        ]);
    }
}
