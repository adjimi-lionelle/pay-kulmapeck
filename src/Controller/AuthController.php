<?php
namespace App\Controller;

use App\Repository\AppUsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


#[Route('/api/app')]
class AuthController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function apiLogin(
        Request $request,
        AppUsersRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Missing email or password'], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['email'];
        $password = $data['password'];

        $user = $userRepository->findOneBy(['email' => $username]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials '], Response::HTTP_UNAUTHORIZED);
        }

        $token = new UsernamePasswordToken(
            $user,
            'main', 
            $user->getRoles()
        );
        $tokenStorage->setToken($token);

        return new JsonResponse([
            'token' => $jwtManager->create($user),
            'username' => $user->getUsername(),
            'roles'=>$user->getRoles()
        ]);
    
    }

}