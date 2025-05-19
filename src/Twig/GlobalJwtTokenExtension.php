<?php
// src/Twig/GlobalJwtTokenExtension.php
namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalJwtTokenExtension extends AbstractExtension implements GlobalsInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getGlobals(): array
    {
        $token = null;
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->getSession()->has('jwt_token')) {
            $token = $request->getSession()->get('jwt_token');
        }
        return [
            'jwt_token' => $token
        ];
    }
}
