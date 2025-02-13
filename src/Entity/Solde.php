<?php

namespace App\Entity;

use App\Repository\SoldeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SoldeRepository::class)]
class Solde
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $entreeBrute = null;

    #[ORM\Column]
    private ?float $sortieBrute = null;

    #[ORM\Column]
    private ?float $entreeNet = null;

    #[ORM\Column]
    private ?float $sortieNet = null;

    #[ORM\Column]
    private ?float $soldeBrute = null;

    #[ORM\Column]
    private ?float $soldeNet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntreeBrute(): ?float
    {
        return $this->entreeBrute;
    }

    public function setEntreeBrute(float $entreeBrute): static
    {
        $this->entreeBrute = $entreeBrute;

        return $this;
    }

    public function getSortieBrute(): ?float
    {
        return $this->sortieBrute;
    }

    public function setSortieBrute(float $sortieBrute): static
    {
        $this->sortieBrute = $sortieBrute;

        return $this;
    }

    public function getEntreeNet(): ?float
    {
        return $this->entreeNet;
    }

    public function setEntreeNet(float $entreeNet): static
    {
        $this->entreeNet = $entreeNet;

        return $this;
    }

    public function getSortieNet(): ?float
    {
        return $this->sortieNet;
    }

    public function setSortieNet(float $sortieNet): static
    {
        $this->sortieNet = $sortieNet;

        return $this;
    }

    public function getSlodeBrute(): ?float
    {
        return $this->soldeBrute;
    }

    public function setSlodeBrute(float $slodeBrute): static
    {
        $this->soldeBrute = $slodeBrute;

        return $this;
    }

    public function getSoldeNet(): ?float
    {
        return $this->soldeNet;
    }

    public function setSoldeNet(float $soldeNet): static
    {
        $this->soldeNet = $soldeNet;

        return $this;
    }
}
