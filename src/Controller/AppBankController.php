<?php
namespace App\Controller;

use App\Entity\AppBank;
use App\Form\AppBankType;
use App\Repository\AppBankRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/app/bank')]
class AppBankController extends AbstractController
{
    #[Route('/', name: 'app_bank_index', methods: ['GET'])]
    public function index(AppBankRepository $appBankRepository): JsonResponse
    {
        $appBanks = $appBankRepository->findAll();

        return $this->json(['app_banks' => $appBanks]);
    }

    #[Route('/new', name: 'app_bank_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $appBank = new AppBank();
        $form = $this->createForm(AppBankType::class, $appBank);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appBank);
            $entityManager->flush();

            return $this->json(['message' => 'Bank created successfully'], Response::HTTP_CREATED);
        }

        return $this->json(['errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_bank_show', methods: ['GET'])]
    public function show(AppBank $appBank): JsonResponse
    {
        return $this->json(['app_bank' => $appBank]);
    }

    #[Route('/{id}/edit', name: 'app_bank_edit', methods: ['PUT'])]
    public function edit(Request $request, AppBank $appBank, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(AppBankType::class, $appBank);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json(['message' => 'Bank updated successfully']);
        }

        return $this->json(['errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_bank_delete', methods: ['DELETE'])]
    public function delete(Request $request, AppBank $appBank, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete'.$appBank->getId(), $request->request->get('_token'))) {
            $entityManager->remove($appBank);
            $entityManager->flush();
            return $this->json(['message' => 'Bank deleted successfully']);
        }
        return $this->json(['error' => 'Invalid CSRF token'], Response::HTTP_BAD_REQUEST);
    }
}
