<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllUsers(Client|UserInterface $client)
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.Client = :Client')
            ->setParameter('Client', $client);

        $query = $qb->getQuery();
        $query->setFetchMode(User::class, "Client", ClassMetadata::FETCH_EAGER);

        return $query->getResult();
    }

    public function findAllUsersWithPagination(Client|UserInterface $client, int $page, int $limit)
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.Client = :Client')
            ->setParameter('Client', $client)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $query = $qb->getQuery();
        $query->setFetchMode(User::class, "Client", ClassMetadata::FETCH_EAGER);

        return $query->getResult();
    }

}
