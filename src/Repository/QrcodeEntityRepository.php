<?php

namespace App\Repository;

use App\Entity\QrcodeEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QrcodeEntity>
 */
class QrcodeEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QrcodeEntity::class);
    }

    public function deleteQrCodes(): void
    {
        $qb = $this->createQueryBuilder(alias: 'q')
            ->delete(delete: QrcodeEntity::class, alias: 'q')
            ->getQuery()
            ->execute()
        ;
    }
}
