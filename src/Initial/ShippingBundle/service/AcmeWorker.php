<?php

namespace Initial\ShippingBundle\service;

use Symfony\Component\Console\Output\NullOutput;
use Mmoreram\GearmanBundle\Command\Util\GearmanOutputAwareInterface;
use Mmoreram\GearmanBundle\Driver\Gearman;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Gearman\Work(
 *     description = "Worker test description",
 *     defaultMethod = "doBackground",
 *     service = "sample.demo.worker"
 * )
 */
class AcmeWorker implements GearmanOutputAwareInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;
    private $container;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->output = new NullOutput();
    }




    /**
     * Test method to run as a job with console output
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 3,
     *     name = "test",
     *     description = "This is a description"
     * )
     */
    public function testA(\GearmanJob $job)
    {
        $this->output->writeln('Job testA done!');

        return true;
    }

    /**
     * Set the output
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }


    /**
     * Test method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     name = "sendNotificationsToUsers",
     *     description = "Sends a list users an awesome notification"
     * )
     */
    public function sendNotificationsToUsers(\GearmanJob $job)
    {
        // Get the data we put in for the job previously.
        $data = json_decode($job->workload(),true);

        foreach($data['user_ids'] as $userId)
        {
            // Some task that takes a long, long time to do.

            $userId->send();
        }
        return true;
    }
}