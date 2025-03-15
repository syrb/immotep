<?php

namespace App\Repository;

use App\Entity\Lease;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lease>
 */
class LeaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lease::class);
    }

    public function save(Lease $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Lease $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Lease[] Returns an array of Lease objects sorted by start date
     */
    public function findAllSortedByStartDate(): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Lease[] Returns an array of active Lease objects
     */
    public function findActiveLeases(): array
    {
        $today = new \DateTime();
        
        return $this->createQueryBuilder('l')
            ->where('l.startDate <= :today')
            ->andWhere('l.endDate >= :today')
            ->andWhere('l.status = :status')
            ->setParameter('today', $today)
            ->setParameter('status', 'Actif')
            ->orderBy('l.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Lease[] Returns Leases for a specific apartment
     */
    public function findByApartment(int $apartmentId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.apartment = :apartmentId')
            ->setParameter('apartmentId', $apartmentId)
            ->orderBy('l.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Lease[] Returns Leases for a specific tenant
     */
    public function findByTenant(int $tenantId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.tenants', 't')
            ->andWhere('t.id = :tenantId')
            ->setParameter('tenantId', $tenantId)
            ->orderBy('l.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}