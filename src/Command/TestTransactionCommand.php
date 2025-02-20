<?php

namespace App\Command;

use App\Entity\AppTransaction;
use App\Entity\AppEnterprise;
use App\Entity\AppBank;
use App\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-transaction',
    description: 'Test d\'ajout d\'une transaction en base de données.',
)]
class TestTransactionCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private $percentageInt;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->percentageInt = 1.8;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer une entreprise existante (ou en créer une si nécessaire)
        $enterprise = $this->entityManager->getRepository(AppEnterprise::class)->find(1);
        if (!$enterprise) {
            $enterprise = new AppEnterprise();
            $enterprise->setEnterpriseName('Kulmapeck Admin');
            $enterprise->setNumContribuable('LT-douala-89075435');
            $enterprise->setEnable(true);
            $enterprise->setEnterpriseToken('XT0Ep2SgWg6ptkauo6g3oxLvdmunahfLBwXjwv3k9vWU5v9jvskGiWXjSFWEcGmw');
            $enterprise->setCreateAt(new \DateTimeImmutable());
            $this->entityManager->persist($enterprise);
            $this->entityManager->flush();
        }

        // Récupérer une banque existante (ou en créer une si nécessaire)
        $bank = $this->entityManager->getRepository(AppBank::class)->find(1);
        if (!$bank) {
            $bank = new AppBank();
            $bank->setBankName('Mobile Money');
            $bank->setBankCode('CM_MOMO');
            $bank->setEnable(true);
            $this->entityManager->persist($bank);
            $this->entityManager->flush();
        }

        // Créer une nouvelle transaction
        $transaction = new AppTransaction();

        $transaction->setAmount(250);
        $transaction->setTransactionCurrency("XAF");
        $transaction->setStatus('SUCCESS');
        $transaction->setReceiver("658334180");
        $transaction->setSender("691310844");
        $transaction->setType('payIn');
        $transaction->setTransactionReason("Test paiement");
        $transaction->setCustomerName("Lionelle");
        $transaction->setCreatAt(new \DateTimeImmutable());
        $transaction->setUpdateAt(new \DateTimeImmutable());
        $transaction->setEnterprise($enterprise);
        $transaction->setBank($bank);
        $amount = 250;
        $util = new Utils();
        $sortieNet = $amount -
        $util->applyPercentIncrease(
            (float) $amount,
            (float) $this->percentageInt
        );
        $transaction->setSoldeEntreeNet($sortieNet);

        // Sauvegarder la transaction
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $output->writeln('Transaction enregistrée avec succès !');

        return Command::SUCCESS;
    }
}

