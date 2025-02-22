<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'AppCommandCheckTransactionsCommand',
    description: 'verifier et mettre a jour letat des transactions',
)]
class AppCommandCheckTransactionsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://staging-kulmapeck.online/api/pay/recall';

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $output->writeln('cURL error: ' . curl_error($ch));
            return Command::FAILURE;
        }

        // Close cURL session
        curl_close($ch);

        // Display the response
        $output->writeln("Response: $response");

        return Command::SUCCESS;
    }
}
