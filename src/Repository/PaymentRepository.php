<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function save(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Payment[] Returns an array of Payment objects sorted by due date
     */
    public function findAllSortedByDueDate(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.dueDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns an array of upcoming Payment objects
     */
    public function findUpcomingPayments(int $limit = 10): array
    {
        $today = new \DateTime();
        
        return $this->createQueryBuilder('p')
            ->where('p.dueDate >= :today')
            ->andWhere('p.status = :status')
            ->setParameter('today', $today)
            ->setParameter('status', 'Non payé')
            ->orderBy('p.dueDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns an array of upcoming Payment objects between two dates
     */
    public function findUpcomingPaymentsBetweenDates(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.dueDate >= :startDate')
            ->andWhere('p.dueDate <= :endDate')
            ->andWhere('p.status = :status')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('status', 'Non payé')
            ->orderBy('p.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns an array of late Payment objects
     */
    public function findLatePayments(): array
    {
        $today = new \DateTime();
        
        return $this->createQueryBuilder('p')
            ->where('p.dueDate < :today')
            ->andWhere('p.status = :status')
            ->setParameter('today', $today)
            ->setParameter('status', 'Non payé')
            ->orderBy('p.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns payments for a specific lease
     */
    public function findByLease(int $leaseId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.lease = :leaseId')
            ->setParameter('leaseId', $leaseId)
            ->orderBy('p.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns payments for a specific apartment
     */
    public function findByApartment(int $apartmentId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.lease', 'l')
            ->join('l.apartment', 'a')
            ->andWhere('a.id = :apartmentId')
            ->setParameter('apartmentId', $apartmentId)
            ->orderBy('p.dueDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns paid payments between two dates
     */
    public function findPaidPaymentsBetweenDates(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.paymentDate >= :startDate')
            ->andWhere('p.paymentDate <= :endDate')
            ->andWhere('p.status = :status')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('status', 'Payé')
            ->orderBy('p.paymentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array Returns a summary of payment status (paid, unpaid, late)
     */
    public function getPaymentsSummary(): array
    {
        $today = new \DateTime();
        
        $qb = $this->createQueryBuilder('p');
        $paid = $qb->select('COUNT(p.id) as count')
            ->where('p.status = :status')
            ->setParameter('status', 'Payé')
            ->getQuery()
            ->getSingleScalarResult();
            
        $qb = $this->createQueryBuilder('p');
        $unpaid = $qb->select('COUNT(p.id) as count')
            ->where('p.status = :status')
            ->andWhere('p.dueDate >= :today')
            ->setParameter('status', 'Non payé')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
            
        $qb = $this->createQueryBuilder('p');
        $late = $qb->select('COUNT(p.id) as count')
            ->where('p.status = :status')
            ->andWhere('p.dueDate < :today')
            ->setParameter('status', 'Non payé')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
            
        return [
            'paid' => $paid,
            'unpaid' => $unpaid,
            'late' => $late,
            'total' => $paid + $unpaid + $late
        ];
    }
}