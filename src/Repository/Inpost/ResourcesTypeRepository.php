<?php

namespace App\Repository\Inpost;

use App\Entity\Inpost\ResourcesType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourcesType>
 *
 * @method ResourcesType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourcesType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourcesType[]    findAll()
 * @method ResourcesType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourcesTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourcesType::class);
    }
}
