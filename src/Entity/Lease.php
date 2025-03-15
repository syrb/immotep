<?php

namespace App\Entity;

use App\Repository\LeaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LeaseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Lease
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'leases')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "L'appartement est obligatoire")]
    private ?Apartment $apartment = null;

    #[ORM\ManyToMany(targetEntity: Tenant::class, inversedBy: 'leases')]
    #[Assert\Count(min: 1, minMessage: "Au moins un locataire doit être associé à ce bail")]
    private Collection $tenants;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date de fin doit être postérieure à la date de début")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant du loyer est obligatoire")]
    #[Assert\Positive(message: "Le loyer doit être positif")]
    private ?float $rentalAmount = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le montant des charges doit être positif ou nul")]
    private ?float $chargesAmount = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le montant du dépôt de garantie doit être positif ou nul")]
    private ?float $depositAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = 'Actif';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'lease', targetEntity: Payment::class, orphanRemoval: true, cascade: ["persist", "remove"])]
    private Collection $payments;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->tenants = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function generateMonthlyPayments(): void
    {
        // On récupère les dates de début et de fin du bail
        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;
        
        // On fixe au premier jour du mois pour le calcul
        $firstDayOfMonth = clone $startDate;
        $firstDayOfMonth->modify('first day of this month');
        
        // On calcule le montant total (loyer + charges)
        $totalAmount = $this->rentalAmount + ($this->chargesAmount ?? 0);
        
        // On stocke les mois déjà existants pour éviter les doublons
        $existingMonths = [];
        foreach ($this->payments as $payment) {
            $existingMonths[] = $payment->getDueDate()->format('Y-m');
        }
        
        // On génère un paiement pour chaque mois
        $currentDate = clone $firstDayOfMonth;
        
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            
            // On ne crée le paiement que s'il n'existe pas déjà
            if (!in_array($monthKey, $existingMonths)) {
                $payment = new Payment();
                $payment->setLease($this);
                $payment->setDueDate(clone $currentDate);
                $payment->setAmount($totalAmount);
                $payment->setStatus('Non payé');
                $this->addPayment($payment);
            }
            
            // On passe au mois suivant
            $currentDate->modify('+1 month');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApartment(): ?Apartment
    {
        return $this->apartment;
    }

    public function setApartment(?Apartment $apartment): static
    {
        $this->apartment = $apartment;

        return $this;
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function getTenants(): Collection
    {
        return $this->tenants;
    }

    public function addTenant(Tenant $tenant): static
    {
        if (!$this->tenants->contains($tenant)) {
            $this->tenants->add($tenant);
        }

        return $this;
    }

    public function removeTenant(Tenant $tenant): static
    {
        $this->tenants->removeElement($tenant);

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getRentalAmount(): ?float
    {
        return $this->rentalAmount;
    }

    public function setRentalAmount(float $rentalAmount): static
    {
        $this->rentalAmount = $rentalAmount;

        return $this;
    }

    public function getChargesAmount(): ?float
    {
        return $this->chargesAmount;
    }

    public function setChargesAmount(?float $chargesAmount): static
    {
        $this->chargesAmount = $chargesAmount;

        return $this;
    }

    public function getDepositAmount(): ?float
    {
        return $this->depositAmount;
    }

    public function setDepositAmount(?float $depositAmount): static
    {
        $this->depositAmount = $depositAmount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setLease($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getLease() === $this) {
                $payment->setLease(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Obtenir les noms des locataires sous forme de chaîne
     */
    public function getTenantsNames(): string
    {
        $names = [];
        foreach ($this->tenants as $tenant) {
            $names[] = $tenant->getFullName();
        }
        return implode(', ', $names);
    }
}