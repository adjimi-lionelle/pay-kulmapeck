<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\AppTransactionRepository;
use App\Service\TransactionService;

#[AsCommand(
    name: 'app:test-transactions',
    description: 'verifier et mettre a jour letat des transactions',
)]
class AppCommandCheckTransactionsCommand extends Command
{
    private TransactionService $transactionService;
    private AppTransactionRepository $appTransactionRepository;

    public function __construct(TransactionService $transactionService, AppTransactionRepository $appTransactionRepository)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
        $this->appTransactionRepository = $appTransactionRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $transactions = $this->appTransactionRepository->findBy(['status' => 'PENDING']);

        foreach ($transactions as $transaction) {
            $this->transactionService->checkTransactionStatus($transaction->getAppTransactionRef());
        }

        return Command::SUCCESS;
    }
}
