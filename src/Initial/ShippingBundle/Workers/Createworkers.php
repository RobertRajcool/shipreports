<?php
use Mmoreram\GearmanBundle\Driver\Gearman;



/**
 * @Gearman\Work(
 *     iterations = 3,
 *     minimumExecutionTime = 3,
 *     timeout = 20,
 *     description = "Worker test description",
 *     defaultMethod = "doBackground",
 *     servers = {
 *         { "host": "127.0.0.1", "port": 80 },
 *
 *     }
 * )
 */
class Createworkers
{
    /**
     * Test method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 3,
     *     minimumExecutionTime = 2,
     *     timeout = 30,
     *     name = "test",
     *     description = "This is a description"
     * )
     */
    public function testA(\GearmanJob $job)
    {
        echo 'Job testA done!' . PHP_EOL;

        return true;
    }

    /**
     * Test method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     defaultMethod = "doLowBackground"
     * )
     */
    public function testB(\GearmanJob $job)
    {
        echo 'Job testB done!' . PHP_EOL;

        return true;
    }



}