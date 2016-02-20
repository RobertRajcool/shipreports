<?php

/**
 * Created by PhpStorm.
 * User: lawrance
 * Date: 17/2/16
 * Time: 2:40 PM
 */
namespace Initial\ShippingBundle\service;


use Symfony\Component\Console\Output\NullOutput;
use Mmoreram\GearmanBundle\Command\Util\GearmanOutputAwareInterface;
use Mmoreram\GearmanBundle\Driver\Gearman;



/**
 * @Gearman\Work(
 *     defaultMethod = "doBackground",
 *     service = "readexcel.worker"
 * )
 *
 * Gearman worker for readexcelsheet
 *
 * Class ReadExcelWorker
 * @package Initial\ShippingBundle\service
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
     * Send after sales communication
     *
     * @param \GearmanJob $job object with after sales communication
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
        $excelsheetvalues = json_decode($job->workload());

    }

}