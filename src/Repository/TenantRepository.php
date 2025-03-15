<?php

namespace App\Repository;

use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tenant>
 */
class TenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tenant::class);
    }

    public function save(Tenant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tenant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Tenant[] Returns an array of Tenant objects
     */
    public function findAllSortedByName(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.lastName', 'ASC')
            ->addOrderBy('t.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Tenant[] Returns an array of Tenant objects
     */
    public function findByApartment(?int $apartmentId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.apartment = :apartmentId')
            ->setParameter('apartmentId', $apartmentId)
            ->orderBy('t.lastName', 'ASC')
            ->addOrderBy('t.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}