<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Task $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Task $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByUserRole ( User $user, ?string $s, bool $admin = false )
    {
        $query = $this->createQueryBuilder('t')
            ->innerJoin('t.assigned_to', 'a')
            ->addSelect('a')
            ->innerJoin('t.created_by', 'c')
            ->addSelect('c');
            //->select('t.id,t.title,t.slug,t.priority,t.status,u.name,t.created_at,c.id as created_by')
            //->join('t.assigned_to', 'u')
            //->join('t.created_by', 'c');

        if ( $admin ) {

            $query->andWhere('c.id = :val')
                ->setParameter('val', $user->getId());

        } else {

            $query->andWhere('a.id = :val')
                ->setParameter('val', $user->getId());

        }

        if ( $s ) {

            $query->andWhere('(t.title LIKE :s) OR (t.priority LIKE :s) OR (t.status LIKE :s) OR (a.name LIKE :s)')
                ->setParameter('s',"%$s%");

        }
            
        return $query->orderBy('t.created_at','DESC')
            ->getQuery();
    }

    // /**
    //  * @return Task[] Returns an array of Task objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
