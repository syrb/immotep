<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * @return array Returns array of years with documents
     */
    public function findYearsWithDocuments(int $userId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT DISTINCT EXTRACT(YEAR FROM document_date) as year
            FROM document
            WHERE owner_id = :userId
            ORDER BY year DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['userId' => $userId]);
        
        return $result->fetchAllAssociative();
    }

    /**
     * @return Document[] Returns documents for a specific year
     */
    public function findByYear(int $year, int $userId): array
    {
        $startDate = new \DateTime($year . '-01-01');
        $endDate = new \DateTime($year . '-12-31');
        
        return $this->createQueryBuilder('d')
            ->where('d.documentDate >= :startDate')
            ->andWhere('d.documentDate <= :endDate')
            ->andWhere('d.owner = :userId')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('userId', $userId)
            ->orderBy('d.documentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @return Document[] Returns all documents for a user
     */
    public function findAllByUser(int $userId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.owner = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('d.documentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}