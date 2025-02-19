<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenExpirationListener
{
    private $security;
    private $jwtTokenManager;

    public function __construct(Security $security, JWTTokenManagerInterface $jwtTokenManager)
    {
        $this->security = $security;
        $this->jwtTokenManager = $jwtTokenManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        // Ignorer certaines routes
        if (in_array($pathInfo, ['/api/app/login', '/api/pay/recall', '/api/pay/email'])) {
            return;
        }

        // Vérifier si l'utilisateur est authentifié via JWT
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Access Denied: User not authenticated.');
        }
    }

    private function isUserAuthenticated(Request $request)
{
    $authorizationHeader = $request->headers->get('Authorization');

    // Ajoute un log pour vérifier si le token est bien reçu
    error_log('Token reçu: ' . $authorizationHeader);

    if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
        return false;
    }
    
    return true;
}


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
