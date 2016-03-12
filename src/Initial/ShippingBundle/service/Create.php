<?php

namespace Initial\ShippingBundle\service;

use Mmoreram\GearmanBundle\Driver\Gearman;

/**
 * @Gearman\Work(
 *     service="createservies"
 * )
 */
class Create
{

    /**
     * Test method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Gearman\Job()
     */
    public function testA(\GearmanJob $job)
    {
        echo 'Job testA done!' . PHP_EOL;

        return true;
    }
}