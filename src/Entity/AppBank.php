<?php

namespace App\Entity;

use App\Repository\AppBankRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppBankRepository::class)]
class AppBank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $bankName = null;

    #[ORM\Column(length: 255)]
    private ?string $bankCode = null;

    #[ORM\Column]
    private ?bool $enable = null;

    #[ORM\OneToMany(mappedBy: 'bank', targetEntity: AppTransaction::class)]
private Collection $bankMoney;
    
    public function __construct()
    {
        $this->bankMoney = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): static
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankCode(): ?string
    {
        return $this->bankCode;
    }

    public function setBankCode(string $bankCode): static
    {
        $this->bankCode = $bankCode;

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

      /**
     * @return Collection<int, AppTransaction>
     */
    public function getBankMoney(): Collection
    {
        return $this->bankMoney;
    }

    public function addBankMoney(AppTransaction $bankMoney): static
    {
        if (!$this->bankMoney->contains($bankMoney)) {
            $this->bankMoney->add($bankMoney);
            $bankMoney->setBank($this);
        }

        return $this;
    }

    public function removeBankMoney(AppTransaction $bankMoney): static
    {
        if ($this->bankMoney->removeElement($bankMoney)) {
            // set the owning side to null (unless already changed)
            if ($bankMoney->getBank() === $this) {
                $bankMoney->setBank(null);
            }
        }

        return $this;
    }
}
