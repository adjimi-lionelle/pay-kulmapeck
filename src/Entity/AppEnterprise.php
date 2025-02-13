<?php

namespace App\Entity;

use App\Repository\AppEntrepriseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppEntrepriseRepository::class)]
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

    #[ORM\OneToMany(mappedBy: 'enterprise', targetEntity: AppTransaction::class)]
    private Collection $bank;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $omNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $momoNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accountNumber = null;

    public function __construct()
    {
        $this->bank = new ArrayCollection();
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

    /**
     * @return Collection<int, AppTransaction>
     */
    public function getBank(): Collection
    {
        return $this->bank;
    }

    public function addBank(AppTransaction $bank): static
    {
        if (!$this->bank->contains($bank)) {
            $this->bank->add($bank);
            $bank->setEnterprise($this);
        }

        return $this;
    }

    public function removeBank(AppTransaction $bank): static
    {
        if ($this->bank->removeElement($bank)) {
            // set the owning side to null (unless already changed)
            if ($bank->getEnterprise() === $this) {
                $bank->setEnterprise(null);
            }
        }

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
}
