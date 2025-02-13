<?php
namespace App\Controller;

use App\Entity\AppBank;
use App\Entity\AppEnterprise;
use App\Entity\AppTransaction;
use App\Repository\AppTransactionRepository;
use App\Repository\SoldeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/app/transaction')]
class AppTransactionController extends AbstractController
{
    private $entityManager;
    private $passwordEncoder;
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(
        AppTransactionRepository $appTransactionRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $appTransactions = [];
        $status = $_GET['status'];
        if ($status === '' || $status == null || $status === 'null') {
            $appTransactions = $appTransactionRepository->findBy(
                [],
                ['id' => 'DESC'],
                100
            );
        } else {
            $appTransactions = $entityManager->getRepository(AppTransaction::class)->findBy(
                [
                    'status' =>
                        strtoupper($status)
                ],
                ['id' => 'DESC'],
                100
            );

        }


        // Serialize the actual data using a serialization group or a custom serializer if needed
        $serializedTransactions = [];
        foreach ($appTransactions as $transaction) {
            // Serialize each transaction entity
            $serializedTransactions = [];
            foreach ($appTransactions as $transaction) {
                $serializedTransactions[] = [
                    'id' => $transaction->getId(),
                    'amount' => $transaction->getAmount(),
                    'status' => $transaction->getStatus(),
                    'createAt' => $transaction->getCreatAt()->format('Y-m-d H:i:s'),
                    'updateAt' => $transaction->getUpdateAt()->format('Y-m-d H:i:s'),
                    'bank' => $transaction->getBank() ? $transaction->getBank()->getBankName() : '',
                    'sender' => $transaction->getSender(),
                    'receiver' => $transaction->getReceiver(),
                    'type' => $transaction->getType(),
                    'reason' => $transaction->getTransactionReason(),
                    'customer_name' => $transaction->getCustomerName(),
                    'fees' => $transaction->getFees(),
                    'enterprise' => $transaction->getEnterprise() ? $transaction->getEnterprise()->getEnterpriseName() : ''
                    // ... Include other fields as needed
                ];
            }

        }
        return $this->json($serializedTransactions);
    }


    #[Route('/new', name: 'app_transaction_new', methods: ['POST'])]
    public function payIn(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);
        if (
            isset($data['amount']) &&
            isset($data['status']) &&
            isset($data['enterprise']) &&
            isset($data['bank'])
        ) {
            $transaction = new AppTransaction();

            $existBank = $entityManager->getRepository(AppBank::class)->findOneBy(['bankCode' => $data['bank']]);
            if (!$existBank) {
                return $this->json(['error' => 'This Bank doesnt exists '], Response::HTTP_BAD_REQUEST);
            }

            $existEnterprise = $entityManager->getRepository(AppEnterprise::class)->findOneBy(['enterpriseToken' => $data['enterprise']]);
            if (!$existEnterprise) {
                return $this->json(['error' => 'This Enterprise doesnt exists '], Response::HTTP_BAD_REQUEST);
            }

            // Set up the entity using the decoded data
            $transaction->setAmount($data['amount']);
            $transaction->setStatus($data['status']);
            $transaction->setReceiver($data['receiver']);
            $transaction->setSender($data['sender']);
            $transaction->setType($data['type'] ?? '');
            $transaction->setCreatAt(new \DateTimeImmutable());
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $transaction->setEnterprise($existEnterprise);
            $transaction->setBank($existBank);

            try {
                $entityManager->persist($transaction);
                $entityManager->flush();

                return $this->json(['message' => 'Transaction created successfully'], Response::HTTP_CREATED);
            } catch (\Exception $e) {
                return $this->json(['error' => 'An error occurred while saving the transaction' . $e], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return $this->json(['error' => 'Required data is missing in the JSON payload'], Response::HTTP_BAD_REQUEST);
        }

    }

    /*
        #[Route('/{id}', name: 'app_transaction_show', methods: ['GET'])]
        public function show(AppTransaction $appTransaction): JsonResponse
        {
            return $this->json(['app_transaction' => $appTransaction]);
        }


        #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['PUT'])]
        public function edit(Request $request, AppTransaction $appTransaction, EntityManagerInterface $entityManager): JsonResponse
        {
            $data = json_decode($request->getContent(), true);

            $form = $this->createForm(AppTransactionType::class, $appTransaction);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                return $this->json(['message' => 'Transaction updated successfully']);
            }
            return $this->json(['errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
        }


        #[Route('/{id}', name: 'app_transaction_delete', methods: ['DELETE'])]
        public function delete(
            Request $request, AppTransaction $appTransaction,
            EntityManagerInterface $entityManager
        ): JsonResponse {
            if ($this->isCsrfTokenValid('delete' . $appTransaction->getId(), $request->request->get('_token'))) {
                $entityManager->remove($appTransaction);
                $entityManager->flush();

                return $this->json(['message' => 'Transaction deleted successfully']);
            }

            return $this->json(['error' => 'Invalid CSRF token'], Response::HTTP_BAD_REQUEST);
        }

    */

    #[Route('/stat', name: 'app_transactions', methods: ['GET'])]
    public function transactions(
        EntityManagerInterface $entityManager,

        AppTransactionRepository $transactionRepository,
        SoldeRepository $soldeRepository
    ): JsonResponse {

        // $transactionRepository = $entityManager->getRepository(AppTransaction::class);

        $currentDate = new \DateTimeImmutable();
        $currentWeekStartDate = $currentDate->modify('last monday')->setTime(0, 0, 0);
        $currentMonthStartDate = $currentDate->modify('first day of this month')->setTime(0, 0, 0);
        $currentYearStartDate = (new \DateTimeImmutable())->setDate($currentDate->format('Y'), 1, 1)->setTime(0, 0, 0);

        $weekTransactions = $transactionRepository->findSuccessTransactionsByDateRange($currentWeekStartDate, $currentDate);
        $monthTransactions = $transactionRepository->findSuccessTransactionsByDateRange($currentMonthStartDate, $currentDate);
        $yearTransactions = $transactionRepository->findSuccessTransactionsByDateRange($currentYearStartDate, $currentDate);
        $successCount = $transactionRepository->countTransactionsByStatus('SUCCESS');
        $pendingCount = $transactionRepository->countTransactionsByStatus('PENDING');
        $failedCount = $transactionRepository->countTransactionsByStatus('FAILED');

        $status = 'SUCCESS';
        $type = 'payIn';

        $dayTotalAmountIn = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentDate, $currentDate);
        $weekTotalAmountIn = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentWeekStartDate, $currentDate);
        $monthTotalAmountIn = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentMonthStartDate, $currentDate);
        $yearTotalAmountIn = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentYearStartDate, $currentDate);
        $totalAmountIn = $transactionRepository->getTotalAmountByType($status, $type);
        $totalAmountInNet = $transactionRepository->getTotalAmountEntreeNet($status, $type);

        $type = 'payOut';
        $dayTotalAmountOut = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentDate, $currentDate);
        $weekTotalAmountOut = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentWeekStartDate, $currentDate);
        $monthTotalAmountOut = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentMonthStartDate, $currentDate);
        $yearTotalAmountOut = $transactionRepository->getTotalAmountByTypeAndDateRange($status, $type, $currentYearStartDate, $currentDate);
        $totalAmountOut = $transactionRepository->getTotalAmountByType($status, $type);
        $totalAmountOutNet = $transactionRepository->getTotalAmountSortieNet($status, $type);

        // Convert the transactions data to arrays (if needed)
        $weekTransactionsArray = $this->transactionsToArray($weekTransactions);
        $monthTransactionsArray = $this->transactionsToArray($monthTransactions);
        $yearTransactionsArray = $this->transactionsToArray($yearTransactions);

        // Create the response data
        $responseData = [
            'weekTransactions' => $weekTransactionsArray,
            'monthTransactions' => $monthTransactionsArray,
            'yearTransactions' => $yearTransactionsArray,
            'successCount' => $successCount,
            'pendingCount' => $pendingCount,
            'failedCount' => $failedCount,
            'dayTotalAmountIn' => $dayTotalAmountIn,
            'weekTotalAmountIn' => $weekTotalAmountIn,
            'monthTotalAmountIn' => $monthTotalAmountIn,
            'yearTotalAmountIn' => $yearTotalAmountIn,
            'dayTotalAmountOut' => $dayTotalAmountOut,
            'weekTotalAmountOut' => $weekTotalAmountOut,
            'monthTotalAmountOut' => $monthTotalAmountOut,
            'yearTotalAmountOut' => $yearTotalAmountOut,
            'totalAmountOut' => $totalAmountOut,
            'totalAmountIn' => $totalAmountIn,
            'totalInNet'=>$totalAmountInNet,
            'totalOutNet'=>$totalAmountOutNet,
            'soldes' => $this->soldeToArray($soldeRepository->findAll()),
        ];

        // Return the JSON response
        return new JsonResponse($responseData);
    }

    // Helper method to convert transactions to arrays
    private function transactionsToArray($transactions)
    {
        $transactionArray = [];
        foreach ($transactions as $transaction) {
            // Convert each transaction entity to an array
            // Modify this part according to your transaction entity structure
            $transactionArray[] = [
                'id' => $transaction->getId(),
                'amount' => $transaction->getAmount(),
                'date' => $transaction->getCreatAt(),
                // Add other fields here
            ];
        }
        return $transactionArray;
    }

    private function soldeToArray($transactions)
    {
        $transactionArray = [];
        foreach ($transactions as $transaction) {
            // Convert each transaction entity to an array
            // Modify this part according to your transaction entity structure
            $transactionArray[] = [
                'soldeEntreeNet' => $transaction->getEntreeNet(),
                'soldeEntreeBrute' => $transaction->getEntreeBrute(),
                'soldeSortieNet' => $transaction->getSortieNet(),
                'soldeSortieBrute' => $transaction->getSortieBrute()
                // Add other fields here
            ];
        }
        return $transactionArray;
    }


}