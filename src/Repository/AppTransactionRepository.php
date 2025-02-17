<?php

namespace App\Repository;

use App\Entity\AppTransaction;
use App\Utils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppTransaction>
 *
 * @method AppTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppTransaction[]    findAll()
 * @method AppTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppTransaction::class);
    }

    //    /**
//     * @return AppTransaction[] Returns an array of AppTransaction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?AppTransaction
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findSuccessfulTransactionsForToday()
    {
        $today = new \DateTimeImmutable('today');
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.creatAt >= :todayStart AND t.creatAt <= :todayEnd')
            ->setParameter('status', 'SUCCESS')
            ->setParameter('todayStart', $today->format('Y-m-d 00:00:00'))
            ->setParameter('todayEnd', $today->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getResult();
    }

    public function findSuccessTransactionsByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.creatAt BETWEEN :startDate AND :endDate')
            ->setParameter('status', 'SUCCESS')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    public function countTransactionsByStatus(string $status)
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalAmountByTypeAndDateRange(string $status, string $type, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as totalAmount')
            ->where('t.status = :status')
            ->andWhere('t.type = :type')
            ->andWhere('t.creatAt BETWEEN :startDate AND :endDate')
            ->setParameter('status', $status)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

   
    public function getTotalAmountByType(string $status, string $type)
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as totalAmount')
            ->where('t.status = :status')
            ->andWhere('t.type = :type')
            ->setParameter('status', $status)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalAmountEntreeNet(string $status, string $type)
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.soldeEntreeNet) as totalAmount')
            ->where('t.status = :status')
            ->andWhere('t.type = :type')
            ->setParameter('status', $status)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalAmountSortieNet(string $status, string $type)
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.soldeSortieNet) as totalAmount')
            ->where('t.status = :status')
            ->andWhere('t.type = :type')
            ->setParameter('status', $status)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

  public function findPendingTransactions(): array
    {
        $currentDateTime = new \DateTimeImmutable();
        $dateTimeThreshold = $currentDateTime->modify('-20 minutes');
    
        return $this->createQueryBuilder('t')
            ->where('t.status = :status OR t.status = :createdStatus') // Added condition for "Created" status
            ->andWhere('t.creatAt >= :threshold')
            ->setParameter('status', Utils::PENDING) // Assuming you have a constant for "pending" status
            ->setParameter('createdStatus', Utils::CREATED) // Assuming you have a constant for "created" status
            ->setParameter('threshold', $dateTimeThreshold)
            ->getQuery()
            ->getResult();
    }
}