<?php

namespace App\Controller;

use App\Entity\AppUsers;
use App\Form\AppUsersType;
use App\Repository\AppUsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('/api/app/users')]

class AppUsersController extends AbstractController
{
    private $entityManager;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/', name: 'app_users_index', methods: ['GET'])]

    public function index(AppUsersRepository $appUsersRepository): JsonResponse
    {
        $users = $appUsersRepository->findAll();
        $usersArray = [];
        foreach ($users as $user) {
            $usersArray[] = [
                'id' => $user->getId(),
                'userName' => $user->getUserName(),
                'surName' => $user->getSurName(),
                'enable' => $user->getEnable(),
                'firstName' => $user->getFirstName(),
                'phone' => $user->getPhone(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
        }
       
        return new JsonResponse($usersArray);
    }

    #[Route('/new', name: 'app_users_new', methods: ['POST'])]

    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $appUser = new AppUsers();
        $form = $this->createForm(AppUsersType::class, $appUser);
        $form->submit($data);

        if ($form->isValid()) {
            $encodedPassword = $this->passwordEncoder->hashPassword($appUser, $appUser->getPassword());
            $appUser->setPassword($encodedPassword);
            $existingUser = $entityManager->getRepository(AppUsers::class)->findOneBy(['userName' => $appUser->getUserName()]);
            if ($existingUser) {
                return $this->json(['error' => 'Email  already exists'], Response::HTTP_BAD_REQUEST);
            }
            $entityManager->persist($appUser);
            $entityManager->flush();

            return $this->json($appUser, Response::HTTP_CREATED);

        }
        return $this->json(['error' => (string) $form->getErrors(true, true)], Response::HTTP_BAD_REQUEST);

    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]

    public function show(AppUsers $appUser): JsonResponse
    {
        return $this->json($appUser);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]

    public function edit(Request $request, AppUsers $appUser): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AppUsersType::class, $appUser);
        $form->submit($data);

        if ($form->isValid()) {
            $this->entityManager->flush();

            return $this->json($appUser);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]

    public function delete(AppUsers $appUser): JsonResponse
    {
        $this->entityManager->remove($appUser);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}