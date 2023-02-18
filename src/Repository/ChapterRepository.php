<?php
namespace App\Repository;

use App\Entity\Chapter;
use Doctrine\ORM\EntityManagerInterface;

class ChapterRepository
{
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Chapter::class);
    }

    /**
     * Return all soft-deleted chapters.
     *
     * @return Chapter[] Returns an array of Chapter objects
     */
    public function findByIsDeleted()
    {
        return $this->repository->createQueryBuilder('c')
            ->andWhere('c.deleteTimestamp IS NOT NULL')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Return the number of soft-deleted chapters.
     *
     * @return int Returns the count of soft-deleted chapters
     */
    public function getCountIsDeleted()
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');
        return $queryBuilder
            ->select($queryBuilder->expr()->count('c.id'))
            ->andWhere('c.deleteTimestamp IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Return all public chapters or all private chapters.
     *
     * @param bool $isPublic Whether to return public chapters or private
     * @return Chapter[] Returns an array of Chapter objects
     */
    public function findByIsPublic(bool $isPublic)
    {
        return $this->repository->createQueryBuilder('c')
            ->andWhere('c.isPublic = :isPublic')
            ->andWhere('c.deleteTimestamp IS NULL')
            ->setParameter('isPublic', $isPublic)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByFolder($folder): ?Chapter
    {
        return $this->repository->createQueryBuilder('c')
            ->andWhere('c.folder = :folder')
            ->setParameter('folder', $folder)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
