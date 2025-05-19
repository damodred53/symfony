<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {

        $session = $request->getSession();
        $session->remove('jwt_token');

        return $this->redirectToRoute('app_home');
    }
}
