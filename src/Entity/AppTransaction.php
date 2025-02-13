<?php

namespace App\Entity;

use App\Repository\AppTransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppTransactionRepository::class)]
class AppTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $amount = null;

    #[ORM\Column]
    private ?String $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $creatAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\ManyToOne(inversedBy: 'bank')]
    private ?AppEnterprise $enterprise = null;

    #[ORM\ManyToOne(inversedBy: 'bankMoney')]
    private ?AppBank $bank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sender = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $receiver = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $app_transaction_ref = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transaction_reason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transaction_currency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customer_name = null;

    #[ORM\Column(nullable: true)]
    private ?float $soldeEntreeNet = null;

    #[ORM\Column(nullable: true)]
    private ?float $soldeSortieNet = null;

    #[ORM\Column(nullable: true)]
    private ?float $fees = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?String
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, Utils::getStatusValues(), true)) {
            throw new \InvalidArgumentException("Invalid status value: $status");
        }

        $this->status = $status;

        return $this;
    }

    public function getCreatAt(): ?\DateTimeImmutable
    {
        return $this->creatAt;
    }

    public function setCreatAt(\DateTimeImmutable $creatAt): static
    {
        $this->creatAt = $creatAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    
    public function getEnterprise(): ?AppEnterprise
    {
        return $this->enterprise;
    }

    public function setEnterprise(?AppEnterprise $enterprise): static
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    public function getBank(): ?AppBank
    {
        return $this->bank;
    }

    public function setBank(?AppBank $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(?string $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?string
    {
        return $this->receiver;
    }

    public function setReceiver(?string $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAppTransactionRef(): ?string
    {
        return $this->app_transaction_ref;
    }

    public function setAppTransactionRef(?string $app_transaction_ref): static
    {
        $this->app_transaction_ref = $app_transaction_ref;

        return $this;
    }

    public function getTransactionReason(): ?string
    {
        return $this->transaction_reason;
    }

    public function setTransactionReason(?string $transaction_reason): static
    {
        $this->transaction_reason = $transaction_reason;

        return $this;
    }

    public function getTransactionCurrency(): ?string
    {
        return $this->transaction_currency;
    }

    public function setTransactionCurrency(?string $transaction_currency): static
    {
        $this->transaction_currency = $transaction_currency;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customer_name;
    }

    public function setCustomerName(?string $customer_name): static
    {
        $this->customer_name = $customer_name;

        return $this;
    }

    public function getSoldeEntreeNet(): ?float
    {
        return $this->soldeEntreeNet;
    }

    public function setSoldeEntreeNet(?float $soldeEntreeNet): static
    {
        $this->soldeEntreeNet = $soldeEntreeNet;

        return $this;
    }

    public function getSoldeSortieNet(): ?float
    {
        return $this->soldeSortieNet;
    }

    public function setSoldeSortieNet(?float $soldeSortieNet): static
    {
        $this->soldeSortieNet = $soldeSortieNet;

        return $this;
    }

    public function getFees(): ?float
    {
        return $this->fees;
    }

    public function setFees(?float $fees): static
    {
        $this->fees = $fees;

        return $this;
    }
}
