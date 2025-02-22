<?php

namespace App\Service;

use App\Entity\AppTransaction;
use App\Repository\AppTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class TransactionService
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;
    private AppTransactionRepository $appTransactionRepository;
    private LoggerInterface $logger;
    private string $publicKey;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        AppTransactionRepository $appTransactionRepository,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->appTransactionRepository = $appTransactionRepository;
        $this->logger = $logger;
        $this->publicKey = "VOTRE_PUBLIC_KEY"; // Remplace avec ta clé publique My-CoolPay
    }

    /**
     * Vérifie le statut d'une transaction sur My-CoolPay
     */
    public function checkTransactionStatus(string $transactionId): array
    {
        $url = "https://my-coolpay.com/api/{$this->publicKey}/checkStatus/{$transactionId}";
        $headers = ['Accept' => 'application/json'];

        // Récupération de la transaction dans la base de données
        $transaction = $this->appTransactionRepository->findOneBy(['app_transaction_ref' => $transactionId]);

        if (!$transaction) {
            $this->logger->warning("Transaction non trouvée : {$transactionId}");
            return ['message' => 'Transaction non trouvée', 'code' => 400];
        }

        try {
            // Appel API pour récupérer le statut de la transaction
            $response = $this->httpClient->request('GET', $url, [
                'headers' => $headers,
            ]);

            $httpCode = $response->getStatusCode();
            $responseData = $response->toArray(false);

            if ($httpCode !== 200 || !isset($responseData['transaction_status'])) {
                $this->logger->error("Erreur API My-CoolPay : " . json_encode($responseData));
                return ['message' => 'Échec de récupération du statut', 'code' => $httpCode];
            }

            // Mise à jour du statut de la transaction
            $transaction->setStatus($responseData['transaction_status']);
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->logger->info("Transaction mise à jour : {$transactionId} - Statut : {$responseData['transaction_status']}");

            // Notifier Kulmapeck après la mise à jour du statut
            $this->notifyKulmapeck($transactionId, $responseData['transaction_status']);

            return ['message' => 'Statut mis à jour', 'status' => $responseData['transaction_status'], 'code' => 200];

        } catch (\Exception $e) {
            $this->logger->error("Erreur API lors de la vérification de la transaction {$transactionId}: " . $e->getMessage());
            return ['message' => 'Erreur lors de la vérification de la transaction', 'error' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * Envoie une notification à Kulmapeck après mise à jour du statut de transaction
     */
    private function notifyKulmapeck(string $transactionId, string $status)
    {
        $redirectUrl = "https://kulmapeck.com/api/pay/callback/?transaction_ref="
            . urlencode($transactionId) . "&status=" . urlencode($status);

        try {
            $this->httpClient->request('GET', $redirectUrl);
            $this->logger->info("Notification envoyée à Kulmapeck : {$redirectUrl}");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la notification à Kulmapeck : " . $e->getMessage());
        }
    }
}
