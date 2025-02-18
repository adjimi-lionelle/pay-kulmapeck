<?php
namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckUserEnabledMiddleware implements HttpKernelInterface
{
    private $app;
    private $tokenStorage;

    public function __construct(HttpKernelInterface $app, TokenStorageInterface $tokenStorage)
    {
        $this->app = $app;
        $this->tokenStorage = $tokenStorage;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response
    {
        
        // Check if the user is logged in
        if ($this->tokenStorage->getToken() !== null) {
            $user = $this->tokenStorage->getToken()->getUser();
           
            // Check if the user is enabled
            if ($user && !$user->getEnable()) {
                return new Response('User is not enabled.', Response::HTTP_FORBIDDEN);
            }
        }

        // Call the next middleware in the chain
        return $this->app->handle($request, $type, $catch);
    }
}





