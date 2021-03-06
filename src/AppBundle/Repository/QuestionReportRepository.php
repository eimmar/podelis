<?php

namespace AppBundle\Repository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * QuestionReportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuestionReportRepository extends \Doctrine\ORM\EntityRepository
{
    const MAX_RESULTS = 20;

    public function paginate($dql, $page = 1, $limit = NotificationRepository::MAX_RESULTS)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $paginator;
    }

    /**
     * @param int $currentPage
     * @param int $userId
     * @param int $limit
     * @return Paginator
     */
    public function getPaginatedReports($currentPage = 1, $userId, $limit = NotificationRepository::MAX_RESULTS)
    {
        $query = $this->createQueryBuilder('n')
            ->select('n')
            ->where('n.created_by = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.created_at', 'desc')
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }
}
