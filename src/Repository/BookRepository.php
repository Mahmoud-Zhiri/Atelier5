<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function searchBookByRef($ref)
{
    return $this->createQueryBuilder('b')
        ->where('b.ref = :ref')
        ->setParameter('ref', $ref)
        ->getQuery()
        ->getOneOrNullResult();
}


public function booksListByAuthors()
{
    return $this->createQueryBuilder('b')
        ->leftJoin('b.auteur', 'a')
        ->addOrderBy('a.nom', 'ASC')
        ->getQuery()
        ->getResult();
}

public function booksPublishedBefore2023WithMoreThan10Books()
{
    return $this->createQueryBuilder('b')
        ->where('b.datePublication < :date')
        ->setParameter('date', '2023-01-01')
        ->andWhere('b.auteur IN (
            SELECT a.id
            FROM Auteur a
            JOIN Livre l
            WHERE a.id = l.auteur
            GROUP BY a.id
            HAVING COUNT(l) > 10
        )')
        ->getQuery()
        ->getResult();
}


public function updateCategoryFromScienceFictionToRomance()
{
    return $this->createQueryBuilder('b')
        ->update('Livre', 'b')
        ->set('b.categorie', ':newCategory')
        ->where('b.categorie = :oldCategory')
        ->setParameter('newCategory', 'Romance')
        ->setParameter('oldCategory', 'Science-Fiction')
        ->getQuery()
        ->execute();
}



    public function countRomanceBooks()
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT(b) FROM App\Entity\Livre b WHERE b.categorie = :category');
        $query->setParameter('category', 'Romance');
        return $query->getSingleScalarResult();
    }


    public function findBooksPublishedBetweenDates($startDate, $endDate)
    {
        $query = $this->createQueryBuilder('b')
            ->where('b.datePublication BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery();

        return $query->getResult();
    }

    public function findAuthorsByBookNum($minBooks, $maxBooks)
    {
        $query = $this->createQueryBuilder('b')
            ->select('a')
            ->join('b.auteur', 'a')
            ->groupBy('a.id')
            ->having('COUNT(b) BETWEEN :minBooks AND :maxBooks')
            ->setParameter('minBooks', $minBooks)
            ->setParameter('maxBooks', $maxBooks)
            ->getQuery();

        return $query->getResult();
    }

    public function deleteAuthorsWithZeroBooks()
    {
        $query = $this->getEntityManager()->createQuery('DELETE FROM App\Entity\Auteur a WHERE NOT EXISTS (SELECT b FROM App\Entity\Livre b WHERE b.auteur = a)');
        $query->execute();
    }   




}

