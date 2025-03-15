<?php

namespace App\Entity;

use App\Repository\TenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tenant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: "/^[0-9]{10}$/", message: "Le numéro de téléphone doit contenir 10 chiffres")]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $guarantorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: "L'email du garant '{{ value }}' n'est pas valide.")]
    private ?string $guarantorEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: "/^[0-9]{10}$/", message: "Le numéro de téléphone du garant doit contenir 10 chiffres")]
    private ?string $guarantorPhone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $guarantorAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idCardFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $leaseContractFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $guarantorDocumentFilename = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'tenants')]
    private ?Apartment $apartment = null;

    // Propriétés pour les fichiers à uploader (non stockées en BDD)
    private $idCardFile;
    private $leaseContractFile;
    private $guarantorDocumentFile;

    #[ORM\ManyToMany(targetEntity: Lease::class, mappedBy: 'tenants')]
    private Collection $leases;

    public function __construct()
    {
        $this->leases = new ArrayCollection();
    }

    public function getIdCardFile()
    {
        return $this->idCardFile;
    }

    public function setIdCardFile($idCardFile): self
    {
        $this->idCardFile = $idCardFile;
        
        if ($idCardFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getLeaseContractFile()
    {
        return $this->leaseContractFile;
    }

    public function setLeaseContractFile($leaseContractFile): self
    {
        $this->leaseContractFile = $leaseContractFile;
        
        if ($leaseContractFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getGuarantorDocumentFile()
    {
        return $this->guarantorDocumentFile;
    }

    public function setGuarantorDocumentFile($guarantorDocumentFile): self
    {
        $this->guarantorDocumentFile = $guarantorDocumentFile;
        
        if ($guarantorDocumentFile) {
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

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getGuarantorName(): ?string
    {
        return $this->guarantorName;
    }

    public function setGuarantorName(?string $guarantorName): static
    {
        $this->guarantorName = $guarantorName;

        return $this;
    }

    public function getGuarantorEmail(): ?string
    {
        return $this->guarantorEmail;
    }

    public function setGuarantorEmail(?string $guarantorEmail): static
    {
        $this->guarantorEmail = $guarantorEmail;

        return $this;
    }

    public function getGuarantorPhone(): ?string
    {
        return $this->guarantorPhone;
    }

    public function setGuarantorPhone(?string $guarantorPhone): static
    {
        $this->guarantorPhone = $guarantorPhone;

        return $this;
    }

    public function getGuarantorAddress(): ?string
    {
        return $this->guarantorAddress;
    }

    public function setGuarantorAddress(?string $guarantorAddress): static
    {
        $this->guarantorAddress = $guarantorAddress;

        return $this;
    }

    public function getIdCardFilename(): ?string
    {
        return $this->idCardFilename;
    }

    public function setIdCardFilename(?string $idCardFilename): static
    {
        $this->idCardFilename = $idCardFilename;

        return $this;
    }

    public function getLeaseContractFilename(): ?string
    {
        return $this->leaseContractFilename;
    }

    public function setLeaseContractFilename(?string $leaseContractFilename): static
    {
        $this->leaseContractFilename = $leaseContractFilename;

        return $this;
    }

    public function getGuarantorDocumentFilename(): ?string
    {
        return $this->guarantorDocumentFilename;
    }

    public function setGuarantorDocumentFilename(?string $guarantorDocumentFilename): static
    {
        $this->guarantorDocumentFilename = $guarantorDocumentFilename;

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
            $lease->addTenant($this);
        }

        return $this;
    }

    public function removeLease(Lease $lease): static
    {
        if ($this->leases->removeElement($lease)) {
            $lease->removeTenant($this);
        }

        return $this;
    }
}