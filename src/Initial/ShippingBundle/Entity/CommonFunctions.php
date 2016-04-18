<?php
/**
 * Created by PhpStorm.
 * User: lawrance
 * Date: 14/3/16
 * Time: 7:15 PM
 */

namespace Initial\ShippingBundle\Entity;


class CommonFunctions
{


    public function countActiveRecords($category_id = null,$entityname)
    {
        $qb = $this->createQueryBuilder('j')
            ->from($entityname, 'j')
            ->select('count(j.id)');


        if($category_id)
        {
            $qb->andWhere('j.category = :category_id')
                ->setParameter('category_id', $category_id);
        }

        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }


    private $active_jobs;

    // ...

    public function setActiveJobs($jobs)
    {
        $this->active_jobs = $jobs;
    }

    public function getActiveJobs()
    {
        return $this->active_jobs;
    }

    public function getRecords($category_id = null, $max = null, $offset = null,$entityname)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder('j')
            ->orderBy('j.id', 'DESC');

        if($max)
        {
            $qb->setMaxResults($max);
        }

        if($offset)
        {
            $qb->setFirstResult($offset);
        }

        /* if($category_id)
         {
             $qb->andWhere('j.category = :category_id')
                 ->setParameter('category_id', $category_id);
         }*/

        $query = $qb->getQuery();

        return $query->getResult();
    }



}