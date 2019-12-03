<?php
namespace App\Repository;

use App\Entity\Chapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Chapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chapter[]    findAll()
 * @method Chapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapter::class);
    }

    /**
     * Return all public chapters or all private chapters.
     *
     * @param bool $isPublic Whether to return public chapters or private
     * @return Chapter[] Returns an array of Chapter objects
     */
    public function findByIsPublic(bool $isPublic)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isPublic = :isPublic')
            ->andWhere('c.isDeleted = false')
            ->setParameter('isPublic', $isPublic)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByFolder($folder): ?Chapter
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.folder = :folder')
            ->setParameter('folder', $folder)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
