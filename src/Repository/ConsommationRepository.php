<?php

namespace App\Repository;

use App\Entity\Consommation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consommation>
 */
class ConsommationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consommation::class);
    }

    /**
     * @return Consommation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.dateReleve', 'DESC')
            ->addOrderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, float> map [typeEnergieId => total]
     */
    public function getMonthlyTotalsByType(User $user, \DateTimeInterface $monthStart, \DateTimeInterface $nextMonthStart): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('IDENTITY(c.typeEnergie) AS typeId, SUM(c.valeur) AS total')
            ->andWhere('c.user = :user')
            ->andWhere('c.dateReleve >= :monthStart')
            ->andWhere('c.dateReleve < :nextMonthStart')
            ->setParameter('user', $user)
            ->setParameter('monthStart', $monthStart)
            ->setParameter('nextMonthStart', $nextMonthStart)
            ->groupBy('c.typeEnergie')
            ->getQuery()
            ->getArrayResult();

        $totals = [];
        foreach ($rows as $row) {
            $typeId = (int) ($row['typeId'] ?? 0);
            if ($typeId <= 0) {
                continue;
            }

            $totals[$typeId] = (float) ($row['total'] ?? 0);
        }

        return $totals;
    }
}
