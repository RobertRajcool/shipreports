<?php

/**
 * Created by PhpStorm.
 * User: lawrance
 * Date: 17/2/16
 * Time: 2:40 PM
 */
namespace Initial\ShippingBundle\Workers;


use Symfony\Component\Console\Output\NullOutput;
use Mmoreram\GearmanBundle\Command\Util\GearmanOutputAwareInterface;
use Mmoreram\GearmanBundle\Driver\Gearman;
use Initial\ShippingBundle\Entity\ReadingKpiValues;



/**
 * @Gearman\Work(
 *     defaultMethod = "doBackground",
 *     service = "readexcel.worker"
 * )
 *
 * Gearman worker for readexcelsheet
 *
 * Class ReadExcelWorker
 * @package Initial\ShippingBundle\Workers
 */
class ReadExcelWorker
{




    private $container;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    /**
     * Insert after reading kpi values
     *
     * @param \GearmanJob $job Insert after reading kpi values
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 1,
     *     name = "readexcelsheet"
     * )
     */
    public function readExcelSheet(\GearmanJob $job)
    {
        $parametervalues = json_decode($job->workload());
        $shipid = $parametervalues['shipDetailsId'];
        $kpiid = $parametervalues['kpiDetailsId'];
        $elementId = $parametervalues['elementDetailsId'];
        $month = $parametervalues['monthdetail'];
        $value = $parametervalues['value'];
        $monthtostring=$month['year'].'-'.$month['month'].'-'.$month['day'];
        $new_date=new \DateTime($monthtostring);
        $new_date->modify('first day of this month');


        $em = $this->getDoctrine()->getManager();
        $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiid));
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementId));
        $readingkpivalue=new ReadingKpiValues();
        $readingkpivalue->setKpiDetailsId($newkpiid);
        $readingkpivalue->setElementDetailsId($newelementid);
        $readingkpivalue->setShipDetailsId($newshipid);
        $readingkpivalue->setMonthdetail($new_date);
        $readingkpivalue->setValue($value);


        $em->persist($readingkpivalue);
        $em->flush();
        return true;


    }

}