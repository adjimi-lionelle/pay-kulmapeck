<?php

namespace App\Entity;

use App\Repository\AppEnterpriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppEnterpriseRepository::class)]
class AppEnterprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $enterpriseName = null;

    #[ORM\Column(length: 255)]
    private ?string $numContribuable = null;

    #[ORM\Column]
    private ?bool $enable = null;

    #[ORM\Column(length: 255)]
    private ?string $enterpriseToken = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;
/*
    #[ORM\OneToMany(mappedBy: 'enterprise', targetEntity: AppBank::class)]
    private Collection $bank;*/
    #[ORM\OneToMany(mappedBy: 'enterprise', targetEntity: AppTransaction::class)]
    private Collection $transactions;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $omNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $momoNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accountNumber = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnterpriseName(): ?string
    {
        return $this->enterpriseName;
    }

    public function setEnterpriseName(string $enterpriseName): static
    {
        $this->enterpriseName = $enterpriseName;

        return $this;
    }

    public function getNumContribuable(): ?string
    {
        return $this->numContribuable;
    }

    public function setNumContribuable(string $numContribuable): static
    {
        $this->numContribuable = $numContribuable;

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function getEnterpriseToken(): ?string
    {
        return $this->enterpriseToken;
    }

    public function setEnterpriseToken(string $enterpriseToken): static
    {
        $this->enterpriseToken = $enterpriseToken;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }


    public function getOmNumber(): ?string
    {
        return $this->omNumber;
    }

    public function setOmNumber(?string $omNumber): static
    {
        $this->omNumber = $omNumber;

        return $this;
    }

    public function getMomoNumber(): ?string
    {
        return $this->momoNumber;
    }

    public function setMomoNumber(?string $momoNumber): static
    {
        $this->momoNumber = $momoNumber;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
 * @return Collection<int, AppTransaction>
 */
public function getTransactions(): Collection
{
    return $this->transactions;
}

public function addTransaction(AppTransaction $transaction): static
{
    if (!$this->transactions->contains($transaction)) {
        $this->transactions->add($transaction);
        $transaction->setEnterprise($this);
    }

    return $this;
}

public function removeTransaction(AppTransaction $transaction): static
{
    if ($this->transactions->removeElement($transaction)) {
        // Set the owning side to null (unless already changed)
        if ($transaction->getEnterprise() === $this) {
            $transaction->setEnterprise(null);
        }
    }

    return $this;
}
}
