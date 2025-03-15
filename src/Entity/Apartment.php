<?php

namespace App\Entity;

use App\Repository\ApartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApartmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Apartment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'appartement est obligatoire")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    private ?string $address = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date d'achat est obligatoire")]
    private ?\DateTimeInterface $purchaseDate = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix d'achat est obligatoire")]
    #[Assert\Positive(message: "Le prix d'achat doit être positif")]
    private ?float $purchasePrice = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le loyer mensuel est obligatoire")]
    #[Assert\Positive(message: "Le loyer mensuel doit être positif")]
    private ?float $rentalPrice = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant des charges est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le montant des charges doit être positif ou nul")]
    private ?float $charges = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La superficie est obligatoire")]
    #[Assert\Positive(message: "La superficie doit être positive")]
    private ?int $size = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = 'Vacant';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tenantName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $bedrooms = null;

    #[ORM\Column(nullable: true)]
    private ?int $bathrooms = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFilename = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'apartment', targetEntity: Tenant::class)]
    private Collection $tenants;

    #[ORM\OneToMany(mappedBy: 'apartment', targetEntity: Lease::class)]
    private Collection $leases;
    
    // Propriété utilisée uniquement pour le formulaire (non stockée en BDD)
    private $imageFile;
    
    public function __construct()
    {
        $this->tenants = new ArrayCollection();
        $this->leases = new ArrayCollection();
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        
        if ($imageFile) {
            // Mettre à jour la date de modification pour déclencher la mise à jour de l'entité
            $this->updatedAt = new \DateTime();
        }

        return $this;
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(\DateTimeInterface $purchaseDate): static
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(float $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getRentalPrice(): ?float
    {
        return $this->rentalPrice;
    }

    public function setRentalPrice(float $rentalPrice): static
    {
        $this->rentalPrice = $rentalPrice;

        return $this;
    }

    public function getCharges(): ?float
    {
        return $this->charges;
    }

    public function setCharges(float $charges): static
    {
        $this->charges = $charges;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

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

    public function getTenantName(): ?string
    {
        return $this->tenantName;
    }

    public function setTenantName(?string $tenantName): static
    {
        $this->tenantName = $tenantName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function setBedrooms(?int $bedrooms): static
    {
        $this->bedrooms = $bedrooms;

        return $this;
    }

    public function getBathrooms(): ?int
    {
        return $this->bathrooms;
    }

    public function setBathrooms(?int $bathrooms): static
    {
        $this->bathrooms = $bathrooms;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): static
    {
        $this->imageFilename = $imageFilename;

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
            $tenant->setApartment($this);
        }

        return $this;
    }

    public function removeTenant(Tenant $tenant): static
    {
        if ($this->tenants->removeElement($tenant)) {
            // set the owning side to null (unless already changed)
            if ($tenant->getApartment() === $this) {
                $tenant->setApartment(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, Lease>
     */
    public function getLeases(): Collection
    {
        return $this->leases;
    }

    public function addLease(Lease $lease): static
    {
        if (!$this->leases->contains($lease)) {
            $this->leases->add($lease);
            $lease->setApartment($this);
        }

        return $this;
    }

    public function removeLease(Lease $lease): static
    {
        if ($this->leases->removeElement($lease)) {
            // set the owning side to null (unless already changed)
            if ($lease->getApartment() === $this) {
                $lease->setApartment(null);
            }
        }

        return $this;
    }
}