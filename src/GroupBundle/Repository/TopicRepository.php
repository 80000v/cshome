<?php

namespace GroupBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GroupBundle\Entity\Group;
use GroupBundle\Entity\Topic;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @author Wenming Tang <wenming@cshome.com>
 */
class TopicRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function queryLatest()
    {
        return $this->createQueryBuilder('t')
            ->select('t,u,g,c')
            ->join('t.user', 'u')
            ->join('t.group', 'g')
            ->leftJoin('t.lastComment', 'c')
            ->where('t.deletedAt IS NULL')
            ->orderBy('t.touchedAt', 'DESC');
    }

    /**
     * @param Group $group
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function queryLatestByGroup(Group $group)
    {
        return $this->queryLatest()
            ->andWhere('t.group = :group')
            ->setParameter('group', $group);
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function findLatest($page = 1)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest(), false));
        $paginator->setMaxPerPage(Topic::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param Group $group
     * @param int   $page
     *
     * @return Pagerfanta
     */
    public function findLatestByGroup(Group $group, $page = 1)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatestByGroup($group), false));
        $paginator->setMaxPerPage(Topic::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
