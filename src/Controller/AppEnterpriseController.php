<?php

namespace App\Controller;

use App\Entity\AppUsers;
use App\Entity\AppEnterprise;
use App\Form\AppEnterpriseType;
use App\Repository\AppEnterpriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/app/enterprise')]
class AppEnterpriseController extends AbstractController

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

    #[Route('/', name: 'app_enterprise_index', methods: ['GET'])]
    public function index(AppEnterpriseRepository $appEnterpriseRepository): JsonResponse
    {
        $appEnterprises = $appEnterpriseRepository->findAll();
        // Process fetched data and prepare it for response
        $responseData = [];
        foreach ($appEnterprises as $appEnterprise) {
            $responseData[] = [
                'enterpriseName' => $appEnterprise->getEnterpriseName(),
                'numContribuable' => $appEnterprise->getNumContribuable(),
                'enable' => $appEnterprise->isEnable(),
                'enterpriseToken' => $appEnterprise->getEnterpriseToken(),
                'omNumber' => $appEnterprise->getOmNumber(),
                'momoNumber' => $appEnterprise->getMomoNumber(),
                'accountNumber' => $appEnterprise->getAccountNumber(),
                'createAt' => $appEnterprise->getCreateAt()->format('Y-m-d\TH:i:s.u\O'),
                // Add other properties
            ];
        }

        return $this->json( $responseData);
    }

    #[Route('/new', name: 'app_enterprise_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $appEnterprise = new AppEnterprise();
       
        $appUser = new AppUsers();
        $enterpriseName = $data['enterpriseName'];
        $numContribuable = $data['numContribuable'];
        $enable =$data['enable'];
        $enterpriseToken = $data['enterpriseToken'];
        $omNumber = $data['omNumber'];
        $momoNumber = $data['momoNumber'];
        $accountNumber = $data['accountNumber'];
        $email = $data['emailACC'];
        $password = $data['password'];
        $phone = $data['phone'];

        $encodedPassword = $this->passwordEncoder->hashPassword($appUser, $password);
        $appUser->setPassword($encodedPassword);
        $existingUser = $entityManager->getRepository(AppUsers::class)->findOneBy(['userName' => $appUser->getUserName()]);
        if ($existingUser) {
            return $this->json(['error' => 'Email  already exists'], Response::HTTP_BAD_REQUEST);
        }

        // Set values in the entity
        $appEnterprise->setEnterpriseName($enterpriseName);
        $appEnterprise->setNumContribuable($numContribuable);
        $appEnterprise->setEnable($enable);
        $appEnterprise->setEnterpriseToken($enterpriseToken);
        $appEnterprise->setOmNumber($omNumber);
        $appEnterprise->setMomoNumber($momoNumber);
        $appEnterprise->setAccountNumber($accountNumber);
        $appEnterprise->setCreateAt(new \DateTimeImmutable());

        // Persist and flush
        $enterprise = $entityManager->persist($appEnterprise);
        $entityManager->flush();
        $appUser->setUserEnterprise($enterprise);
        $appUser->setEmail($email);
        $appUser->setEnable(true);
        $appUser->setPhone($phone);
        $appUser->setUserName($email);
        $appUser->setSurName($enterpriseName);
        $appUser->setFirstName($enterpriseName);
        $entityManager->persist($appUser);
        $entityManager->flush();

        return $this->json(['message' => 'Enterprise created successfully'], Response::HTTP_CREATED);

    }

    #[Route('/{id}', name: 'app_enterprise_show', methods: ['GET'])]
    public function show(AppEnterprise $appEnterprise): JsonResponse
    {
        return $this->json(['app_enterprise' => $appEnterprise]);
    }



    #[Route('/{id}/edit', name: 'app_enterprise_edit', methods: ['PUT'])]
    public function edit(Request $request, AppEnterprise $appEnterprise, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(AppEnterpriseType::class, $appEnterprise);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->json(['message' => 'Enterprise updated successfully']);
        }
        return $this->json(['errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }


    #[Route('/{id}', name: 'app_enterprise_delete', methods: ['DELETE'])]
    public function delete(Request $request, AppEnterprise $appEnterprise, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $appEnterprise->getId(), $request->request->get('_token'))) {
            $entityManager->remove($appEnterprise);
            $entityManager->flush();

            return $this->json(['message' => 'Enterprise deleted successfully']);
        }
        return $this->json(['error' => 'Invalid CSRF token'], Response::HTTP_BAD_REQUEST);
    }
}