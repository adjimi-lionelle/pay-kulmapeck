<?php

namespace App\EventListener;

use App\Service\ApiKeys;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\AppUsersRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class TokenExpirationListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $jwtTokenManager;
    private $userRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtTokenManager,
        AppUsersRepository $userRepository,
        ApiKeys $apiKeys
    ) {
        $this->tokenStorage = $apiKeys->getAPIToken();
        $this->jwtTokenManager = $jwtTokenManager;
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        // Skip the check for /app/auth/login route
        if ($pathInfo === '/api/app/login') {
            return;
        }

        if ($pathInfo === '') {
            return;
        }
        if ($pathInfo === '/') {
            return;
        }

        if ($pathInfo === '/api/pay/recall') {
            return;
        }
        if ($pathInfo === '/api/pay/email') {
            return;
        }

        if (strpos($pathInfo, '/api/pay') === 0) {

            $headerValue = $request->headers->get('X-PRIVATE-KEY');

            // Compare the header value with the stored private key
            if ($headerValue !== $this->tokenStorage) {
                // Unauthorized response
                throw new AccessDeniedHttpException('Access Denied: User not authenticated.');
            }
            return;
        }

        // Check if the user is authenticated with a valid token
        if (!$this->isUserAuthenticated($request)) {
            throw new AccessDeniedHttpException('Access Denied: User not authenticated.');
        }

        // Check if the session token is not expired
        if (!$this->isSessionTokenExpired($request)) {
            throw new AccessDeniedHttpException('Access Denied: Session token is expired.');
        }


        if (!$this->isUserEnabled($request)) {
            throw new AccessDeniedHttpException('Access Denied: User is not enabled.');
        }
    }

    private function isUserAuthenticated(Request $request)
    {
        // Retrieve the bearer token from the Authorization header
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
            return false;
        }
        return true;
    }

    private function isSessionTokenExpired(Request $request)
    {

        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
           // var_dump('Aucun token reçu ou mal formé');
            return null;
        }
        $token = $matches[1];

        // Validate the token's expiration
        $tokenParts = explode('.', $token); // Assuming JWT format
        if (count($tokenParts) !== 3) {
            return null; // Invalid token format
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);
        if (!isset($payload['exp'])) {
            return null; // Token doesn't have an expiration claim
        }

        $expirationTimestamp = $payload['exp'];
        $currentTimestamp = time();

        if ($currentTimestamp >= $expirationTimestamp) {
            throw new AccessDeniedHttpException('Token has expired');
        }

        return $token;
    }

    private function getTokenFromRequest(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
            return null;
        }
        return $matches[1];
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
        // Create a token and authenticate it

        $user = $this->userRepository->findOneBy(['email' => $payload['username']]);
        return $user->getEnable();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}