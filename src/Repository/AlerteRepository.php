<?php

namespace App\Repository;

use App\Entity\Alerte;
use App\Entity\Logement;
use App\Entity\TypeEnergie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alerte>
 */
class AlerteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alerte::class);
    }

    /**
     * @return Alerte[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Alerte[]
     */
    public function findUnreadByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.lu = :isRead')
            ->setParameter('user', $user)
            ->setParameter('isRead', false)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Alerte[]
     */
    public function findTriggeredByConsumption(User $user, Logement $logement, ?TypeEnergie $typeEnergie, float $valeur): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.logement = :logement')
            ->andWhere('a.seuil IS NOT NULL')
            ->andWhere(':valeur >= a.seuil')
            ->setParameter('user', $user)
            ->setParameter('logement', $logement)
            ->setParameter('valeur', $valeur)
            ->orderBy('a.createdAt', 'DESC');

        if (null === $typeEnergie) {
            $qb->andWhere('a.typeEnergie IS NULL');
        } else {
            $qb->andWhere('(a.typeEnergie = :typeEnergie OR a.typeEnergie IS NULL)')
                ->setParameter('typeEnergie', $typeEnergie);
        }

        return $qb->getQuery()->getResult();
    }
}
