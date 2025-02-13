<?php
namespace App\Controller;

use App\Entity\AppBank;
use App\Entity\AppEnterprise;
use App\Entity\AppTransaction;
use App\Entity\Solde;
use App\Repository\AppTransactionRepository;
use App\Service\ApiKeys;
use App\Utils;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pay')]
class PaymentController extends AbstractController
{
    private $publicKey;
    private $privateKey;

    private $cacert;

    private $percentageInt;
    private $percentageOut;

    public function __construct(ApiKeys $apiKeys = null)
    {
        $this->privateKey = $apiKeys->getPrivateKey();
        $this->publicKey = $apiKeys->getPublicKey();
        $this->cacert = $apiKeys->getCacert();

        $this->percentageInt = 1.8;
        $this->percentageOut = 0.5;
        $this->percentageOutTeach = 1;

    }
    /**
     * Paiement par les eleves ou client de kulmapeck vers coolpay
     */

    #[Route('/in', name: 'app_pay_in', methods: ['POST'])]

    public function payIn(
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $url = 'https://my-coolpay.com/api/' . $this->publicKey . '/paylink';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $requestData = json_decode($request->getContent(), true);
        $enterpriseToken = $request->headers->get('X-PRIVATE-KEY');

        // Check if the JSON data is valid
        if ($requestData === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }
        $transaction = new AppTransaction();

        $existBank = null;

        $existBank = $entityManager->getRepository(AppBank::class)->
            findOneBy(['bankCode' => $requestData['transaction_operator']]);

        $existEnterprise = $entityManager->getRepository(AppEnterprise::class)->
            findOneBy(['enterpriseToken' => $enterpriseToken]);

        $existEnterprise = $entityManager->getRepository(AppEnterprise::class)->
            findOneBy(['enterpriseToken' => $enterpriseToken]);
        if (!$existEnterprise) {
            return $this->json(['error' => 'This Enterprise doesnt exists '], Response::HTTP_BAD_REQUEST);
        }

        /*   $data = [
        "transaction_amount" => 500,
        "transaction_currency" => "XAF",
        "transaction_reason" => "Customer refund",
        "transaction_operator" => "CM_OM",
        "app_transaction_ref" => "refund_123",
        "customer_phone_number" => "699009900",
        "customer_name" => "Bob MARLEY",
        "customer_email" => "bob@mail.com",
        "customer_lang" => "en",
        ];*/

        $transaction->setAmount($requestData['transaction_amount']);
        $transaction->setTransactionCurrency($requestData['transaction_currency']);
        $transaction->setStatus('PENDING');
        $transaction->setReceiver($requestData['transaction_receiver']);
        $transaction->setSender($requestData['customer_phone_number']);
        $transaction->setType('payIn');
        $transaction->setTransactionReason($requestData['transaction_reason']);
        $transaction->setCustomerName($requestData['customer_name']);
        $transaction->setCreatAt(new \DateTimeImmutable());
        $transaction->setUpdateAt(new \DateTimeImmutable());
        $transaction->setEnterprise($existEnterprise);
        $transaction->setBank($existBank);
        $amount = $requestData['transaction_amount'];
        $util = new Utils();
        $sortieNet = $amount -
        $util->applyPercentIncrease(
            (float) $amount,
            (float) $this->percentageInt
        );
        $transaction->setSoldeEntreeNet($sortieNet);
        $entityManager->persist($transaction);
        $entityManager->flush();

        // Build the data for the cURL request
        $data = $this->buildPayInFromArray($requestData);
        // Initialize a new cURL handle
        $curl = curl_init();

        // Set cURL options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_CAINFO, $this->cacert);

        // Execute the cURL request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Check for cURL errors
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            dump($error);
        }

        // Close the cURL handle
        curl_close($curl);

        // Debugging information

        $responseData = json_decode($response, true);

        if (isset($responseData['transaction_ref'])) {

            $transaction_ref = $responseData['transaction_ref'];
            $paymentUrl = $responseData['payment_url'];

            $transaction->setStatus('PENDING');
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $transaction->setAppTransactionRef($transaction_ref);
            $entityManager->persist($transaction);
            $entityManager->flush();

            $entityManager->flush();
            return new JsonResponse($responseData, Response::HTTP_ACCEPTED);
        } else {
            $transaction->setStatus('FAILED');
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $entityManager->persist($transaction);
            $entityManager->flush();
            // Payment failed or status not 'success'
            return new JsonResponse($responseData, Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * Retrait par les Enseignants ou client de Coolpay vers Kulmapeck
     */

    #[Route('/out', name: 'app_pay_out', methods: ['POST'])]
    function payOut(
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $url = 'https://my-coolpay.com/api/' . $this->publicKey . '/payout';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-PRIVATE-KEY: ' . $this->privateKey,
        ];

        $requestData = json_decode($request->getContent(), true);
        $enterpriseToken = $request->headers->get('X-PRIVATE-KEY');

        if ($requestData === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $transaction = new AppTransaction();

        $existBank = $entityManager->getRepository(AppBank::class)->
            findOneBy(['bankCode' => $requestData['transaction_operator']]);
        if (!$existBank) {
            return $this->json([
                'error' => 'This Bank doesnt exists with code '
                . $requestData['transaction_operator'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $existEnterprise = $entityManager->getRepository(AppEnterprise::class)->
            findOneBy(['enterpriseToken' => $enterpriseToken]);
        if (!$existEnterprise) {
            return $this->json(['error' => 'This Enterprise doesnt exists '], Response::HTTP_BAD_REQUEST);
        }
        $transaction->setAmount($requestData['transaction_amount']);
        $transaction->setTransactionCurrency($requestData['transaction_currency']);
        $transaction->setStatus('PENDING');
        $transaction->setReceiver($requestData['transaction_receiver']);
        $transaction->setSender($requestData['customer_phone_number']);
        $transaction->setType('payOut');
        $transaction->setTransactionReason($requestData['transaction_reason']);
        $transaction->setCustomerName($requestData['customer_name']);
        $transaction->setCreatAt(new \DateTimeImmutable());
        $transaction->setUpdateAt(new \DateTimeImmutable());
        $transaction->setEnterprise($existEnterprise);
        $transaction->setBank($existBank);
        $amount = $requestData['transaction_amount'];
        $util = new Utils();
        $sortieNet = $amount -
        $util->applyPercentIncrease(
            (float) $amount,
            (float) $this->percentageOut
        );
        $transaction->setSoldeSortieNet($sortieNet);
        $entityManager->persist($transaction);
        $entityManager->flush();

        $data = $this->buildPayOutFromArray($requestData);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_CAINFO, $this->cacert);

        // Check for cURL errors
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            dump($error);
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $responseData = json_decode($response, true);

        if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {

            $transaction_ref = $responseData['transaction_ref'];
            $transaction->setStatus('SUCCESS');
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $transaction->setAppTransactionRef($transaction_ref);
            $transaction->setFees($responseData['transaction_fees']);
            $entityManager->flush();

            return new JsonResponse([
                'message' => 'Payout successful'
                ,
                'transaction_ref' => $transaction_ref,
            ], Response::HTTP_CREATED);

        } else {
            $transaction->setStatus('FAILED');
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $entityManager->flush();
            // HTTP request failed
            return new JsonResponse($responseData, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/recall')]
    function getPending(
        AppTransactionRepository $transactionRepository,
        EntityManagerInterface $entityManager,
        AppTransactionRepository $appTransactionRepository
    ) {
        $pendingTransactions = $transactionRepository->findPendingTransactions();
        $responses = [];

        foreach ($pendingTransactions as $transaction) {
            $transactionId = $transaction->getAppTransactionRef();
            $response = $this->checkTransactionStatus($transactionId, $entityManager, $appTransactionRepository);

            if ($response instanceof JsonResponse) {
                // Check if the response is JSON
                $responseData = json_decode($response->getContent(), true);

                $responses[] = $responseData;

            } else {
                $responses[] = $response;
            }
        }

        // You can return the collected JSON responses as an array
        return new JsonResponse(['responses' => $responses, 'message' => 'Processing pending transactions'], Response::HTTP_OK);
    }

    function checkTransactionStatus(
        $transactionId
        ,
        EntityManagerInterface $entityManager,
        AppTransactionRepository $appTransactionRepository
    ) {
        $url = 'https://my-coolpay.com/api/' . $this->publicKey . '/checkStatus/' . $transactionId;
        $headers = [
            'Accept: application/json',
        ];
        $transaction = new AppTransaction();

        $transaction = $appTransactionRepository->findOneBy(['app_transaction_ref' => $transactionId]);
        if ($transaction === null) {
            return new JsonResponse(['message' => 'Invalid app_transaction_ref data'], Response::HTTP_BAD_REQUEST);
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CAINFO, $this->cacert);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $responseData = json_decode($response, true);

        if (isset($responseData['status'])) {
            // Transaction status retrieved successfully

            $transaction->setStatus($responseData['transaction_status']);
            $transaction->setUpdateAt(new \DateTimeImmutable());
            $entityManager->flush();

            $redirectUrl = 'https://kulmapeck.com/api/pay/callback/?transaction_ref='
            . urlencode($responseData['transaction_ref']) . '&status=' . urlencode($responseData['transaction_status']);

            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $redirectUrl); // Set the URL to request
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any redirects

            // Execute cURL session and store the response
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                echo 'cURL error: ' . curl_error($ch);
            }

            // Close cURL session
            curl_close($ch);
            return ['message'=>$response,'code'=>$httpCode];
            //return new JsonResponse($responseData);
        } else {
            // Transaction status retrieval failed or status not 'success'
            return ['message' => 'Transaction status retrieval failed'];
        }

    }

    function buildPayOutFromArray(array $requestData)
    {
        return [
            "transaction_amount" => $requestData['transaction_amount'] ?? null,
            "transaction_currency" => $requestData['transaction_currency'] ?? null,
            "transaction_reason" => $requestData['transaction_reason'] ?? null,
            "app_transaction_ref" => $requestData['app_transaction_ref'] ?? null,
            "customer_phone_number" => $requestData['customer_phone_number'] ?? null,
            "customer_name" => $requestData['customer_name'] ?? null,
            "customer_email" => $requestData['customer_email'] ?? null,
            "customer_lang" => $requestData['customer_lang'] ?? null,
            "transaction_operator" => $requestData['transaction_operator'] ?? null,
            //

        ];
    }
    function buildPayInFromArray(array $requestData)
    {
        return [
            "transaction_amount" => $requestData['transaction_amount'] ?? null,
            "transaction_currency" => $requestData['transaction_currency'] ?? null,
            "transaction_reason" => $requestData['transaction_reason'] ?? null,
            "app_transaction_ref" => $requestData['app_transaction_ref'] ?? null,
            "customer_phone_number" => $requestData['customer_phone_number'] ?? null,
            "customer_name" => $requestData['customer_name'] ?? null,
            "customer_email" => $requestData['customer_email'] ?? null,
            "customer_lang" => $requestData['customer_lang'] ?? null,

        ];
    }

    #[Route('/callback', name: 'callback', methods: ['POST'])]
    function callBack(
        Request $request,
        EntityManagerInterface $entityManager,
        AppTransactionRepository $appTransactionRepository
    ) {
        // Check if cool pay sender's IP address
        $senderIp = $request->getClientIp();
        $expectedIp = '15.236.140.89';

        $transaction = new AppTransaction();

        if ($senderIp !== $expectedIp) {
            return new JsonResponse(['message' => 'Unauthorized sender IP'], Response::HTTP_FORBIDDEN);
        }

        $jsonData = json_decode($request->getContent(), true);

        if ($jsonData === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }
        $transaction = $appTransactionRepository->findOneBy(['app_transaction_ref' => $jsonData['app_transaction_ref']]);
        if ($transaction === null) {
            return new JsonResponse(['message' => 'Invalid app_transaction_ref data'], Response::HTTP_BAD_REQUEST);
        }
        $transaction->setStatus($jsonData['transaction_status']);
        $transaction->setUpdateAt(new \DateTimeImmutable());
        $entityManager->flush();

        $redirectUrl = 'https://kulmapeck.com/api/pay/callback/?transaction_ref='
        . urlencode($jsonData['transaction_ref']) . '&status=' . urlencode($jsonData['transaction_status']);

        $ch = curl_init();
     
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $redirectUrl); // Set the URL to request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any redirects

        // Execute cURL session and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        return new JsonResponse($response);

    }

    #[Route('/balance', name: 'balance', methods: ['POST'])]
    function getBalance()
    {
        $url = 'https://my-coolpay.com/api/' . $this->publicKey . '/balance';
        $headers = [
            'Accept: application/json',
            'X-PRIVATE-KEY: ' . $this->privateKey,
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CAINFO, $this->cacert);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);

            if (isset($responseData['balance']) && $responseData['status'] === 'success') {
                // Balance retrieval was successful
                return new JsonResponse($responseData);
            } else {
                // Balance retrieval failed or status not 'success'
                return new JsonResponse(['message' => 'Balance retrieval failed'], Response::HTTP_BAD_REQUEST);
            }
        } else {
            // HTTP request failed
            return new JsonResponse(['message' => 'HTTP request failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/email', name: 'balance', methods: ['POST'])]
    function emailSender()
    {

        $mail = new PHPMailer();

        // SMTP Configuration
        $mail->isSMTP();
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'cafile' => $this->cacert,
            ),
        );
        $mail->Host = 'vps96969.serveur-vps.net'; // Replace with your SMTP server address
        $mail->SMTPAuth = true;
        $mail->Username = 'no-reply@kulmapeck.com'; // Replace with your SMTP username
        $mail->Password = 'tZ5$1DcmSUXXYUY'; // Replace with your SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` for SSL
        $mail->Port = 587; // TCP port to connect to
        $mail->SMTPDebug = 2;
        //$mail->SMTPAutoTLS = false;

        // Sender and Recipient
        $mail->setFrom('no-reply@kulmapeck.com', 'no-reply Kulmapeck'); // Replace with your sender's email and name
        $mail->addAddress('ondouabenoit392@gmail.com', 'Recipient Benito'); // Replace with the recipient's email and name

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Kulma Subject of the Email';
        $mail->Body = 'This is the HTML message body';
        $mail->AltBody = 'This is the plain text version of the email';

        // Send the email
        if ($mail->send()) {
            return new JsonResponse('Email sent successfully!');
        } else {
            return new JsonResponse('Email could not be sent. Mailer Error: ' . $mail->ErrorInfo);
        }
    }

}
