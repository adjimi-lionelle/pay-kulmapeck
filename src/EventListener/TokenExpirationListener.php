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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
