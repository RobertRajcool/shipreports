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
    public function find_options_ComparsionRule($option_Parameter,$dbvalue,$option_Parmeter_Value)
    {
        if($option_Parameter=='equalTo') {
            if($dbvalue==$option_Parmeter_Value)
            {
                return true;
            }
            else
            {
                return false;
            }
        } else if($option_Parameter=='greaterThan') {
            if($dbvalue>$option_Parmeter_Value)
            {
                return true;
            }
            else
            {
                return false;
            }
        } else if($option_Parameter=='lessThan') {
            if($dbvalue<$option_Parmeter_Value)
            {
                return true;
            }
            else
            {
                return false;
            }
        } else if($option_Parameter=='greaterThanEqual') {
            if($dbvalue>=$option_Parmeter_Value)
            {
                return true;
            }
            else
            {
                return false;
            }
        } else if($option_Parameter=='lessThanEqual') {
            if($dbvalue<=$option_Parmeter_Value)
            {
                return true;
            }
            else
            {
                return false;
            }
        } else {
            if($dbvalue>=$option_Parmeter_Value)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    public function find_Avg_Sum_Calculation($string_value_avg_or_sum,$elementvalue,$totalnumberships_inserted)
    {
        if($string_value_avg_or_sum == "Average") {
            return $elementvalue / $totalnumberships_inserted;
        } else if($string_value_avg_or_sum == "Sum") {
            return $elementvalue;
        }
        else {
            return $elementvalue / $totalnumberships_inserted;
        }
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