<?php

namespace App\EventListener;

use App\Service\ApiKeys;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\AppUsersRepository;

class TokenExpirationListener implements EventSubscriberInterface
{
    private $apiToken;
    private $jwtTokenManager;
    private $userRepository;
    private $logger;

    public function __construct(
        JWTTokenManagerInterface $jwtTokenManager,
        AppUsersRepository $userRepository,
        ApiKeys $apiKeys,
        LoggerInterface $logger
    ) {
        $this->apiToken = $apiKeys->getAPIToken();
        $this->jwtTokenManager = $jwtTokenManager;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        // Autoriser certaines routes sans vérification d'authentification
        if (in_array($pathInfo, ['/api/app/login', '/', '', '/api/pay/recall', '/api/pay/email'])) {
            return;
        }

        // Vérifier l'authentification pour les requêtes vers /api/pay
        if (strpos($pathInfo, '/api/pay') === 0) {
            $headerValue = $request->headers->get('X-PRIVATE-KEY');

            if (!$headerValue || $headerValue !== $this->apiToken) {
                $this->logger->warning("Accès refusé : Clé privée invalide pour /api/pay", ['path' => $pathInfo]);
                throw new AccessDeniedHttpException('Access Denied: Invalid private key.');
            }

            // Si clé valide, autoriser la requête
            return;
        }

        // Vérifier l'authentification via JWT
        if (!$this->isUserAuthenticated($request)) {
            $this->logger->warning("Accès refusé : JWT Token non trouvé ou invalide", ['path' => $pathInfo]);
            throw new AccessDeniedHttpException('Access Denied: User not authenticated.');
        }

        // Vérifier si le token est expiré
        if (!$this->isSessionTokenValid($request)) {
            $this->logger->warning("Accès refusé : JWT Token expiré", ['path' => $pathInfo]);
            throw new AccessDeniedHttpException('Access Denied: Session token is expired.');
        }

        // Vérifier si l'utilisateur est actif
        if (!$this->isUserEnabled($request)) {
            $this->logger->warning("Accès refusé : Utilisateur désactivé", ['path' => $pathInfo]);
            throw new AccessDeniedHttpException('Access Denied: User is not enabled.');
        }
    }

    private function isUserAuthenticated(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
            return false;
        }
        return true;
    }

    private function isSessionTokenValid(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
            return false;
        }

        $token = $matches[1];

        // Vérifier l'expiration du token
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            return false; // Format JWT invalide
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);
        if (!isset($payload['exp'])) {
            return false; // Pas de date d'expiration dans le token
        }

        return time() < $payload['exp'];
    }

    private function isUserEnabled(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
            return false;
        }

        $token = $matches[1];
        $tokenParts = explode('.', $token);
        $payload = json_decode(base64_decode($tokenParts[1]), true);

        $user = $this->userRepository->findOneBy(['email' => $payload['username']]);

        return $user ? $user->getEnable() : false;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
