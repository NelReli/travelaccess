<?php

namespace App\Repository;

use App\Entity\Article;
use App\Data\SearchData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Article::class);
        $this->paginator = $paginator;
    }


    // calcul des articles
    public function countArticles(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    

    // calcul des villes sans doublon
    public function countDistinctCities(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.city)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // recherche des 5 derniers articles
    public function findLastArticles(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // recherche des 3 articles les plus vues
    public function findMostViewArticles(int $limit = 3): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    
    // recherche des 3 articles les plus commentés
    public function findMostCommentArticles(int $limit = 3): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.comments', 'c')
            ->addSelect('COUNT(c.id) as HIDDEN commentsCount')
            ->groupBy('a.id')
            ->orderBy('commentsCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }



    // select de toutes les villes
    public function findAllCities(): array
    {
        return array_column(
            $this->createQueryBuilder('a')
                ->select('DISTINCT a.city')
                ->orderBy('a.city', 'ASC')
                ->getQuery()
                ->getArrayResult(),
            'city'
        );
    }


    // select de tous les pays
    public function findAllCountries(): array
    {
        return array_column(
            $this->createQueryBuilder('a')
                ->select('DISTINCT a.country')
                ->orderBy('a.country', 'ASC')
                ->getQuery()
                ->getArrayResult(),
            'country'
        );
    }


    // pagination
    public function paginateArticles(int $page): PaginationInterface  
    { 
        return $this->paginator->paginate( $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC'), 
            $page, 
            5
        ); 
    }


    // pagination et filtre
    public function findSearch(SearchData $search, int $page): PaginationInterface
    {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('a.comments', 'c')
            ->addSelect('COUNT(c.id) as HIDDEN commentsCount');

        // Recherche par titre
        if (!empty($search->q)) {
            $query->andWhere('a.title LIKE :q')
                ->setParameter('q', '%'.$search->q.'%');
        }

        // Recherche par ville
        if (!empty($search->city)) {
            $query->andWhere('a.city = :city')
                ->setParameter('city', $search->city);
        }

        // Recherche par pays
        if (!empty($search->country)) {
            $query->andWhere('a.country = :country')
                ->setParameter('country', $search->country);
        }

        // Tri dynamique
        switch ($search->order) {
            case 'views':
                $query->orderBy('a.views', 'DESC');
                break;
            case 'comments':
                $query->orderBy('commentsCount', 'DESC');
                break;
            case 'accessibility':
                $query->orderBy('a.rating', 'DESC');
                break;
            default:
                $query->orderBy('a.createdAt', 'DESC'); // par défaut
        }

        $query->groupBy('a.id');
        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,        
            $page,      // page actuelle
            5           // Limite par page 
        );
    }




    //    /**
    //     * @return Article[] Returns an array of Article objects
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

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
