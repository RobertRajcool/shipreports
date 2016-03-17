<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chart
 *
 * @ORM\Table(name="chart")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ChartRepository")
 */
class Chart
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\KpiDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="kpiname", referencedColumnName="id")
     * })
     */
    private $kpiname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fromdate", type="date")
     */
    private $fromdate;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="todate", type="date")
     */
    private $todate;

    /**
     * @var string
     *
     * @ORM\Column(name="kpiname", type="string", length=255)
     */
    private $elementDetailsId;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set kpiname
     *
     * @param string $kpiname
     * @return Chart
     */
    public function setKpiname($kpiname)
    {
        $this->kpiname = $kpiname;

        return $this;
    }

    /**
     * Get kpiname
     *
     * @return string 
     */
    public function getKpiname()
    {
        return $this->kpiname;
    }

    /**
     * Set fromdate
     *
     * @param \DateTime $fromdate
     * @return Chart
     */
    public function setFromdate($fromdate)
    {
        $this->fromdate = $fromdate;

        return $this;
    }

    /**
     * Get fromdate
     *
     * @return \DateTime 
     */
    public function getFromdate()
    {
        return $this->fromdate;
    }

    /**
     * @return \DateTime
     */
    public function getTodate()
    {
        return $this->todate;
    }

    /**
     * @param \DateTime $todate
     */
    public function setTodate($todate)
    {
        $this->todate = $todate;
    }

    /**
     * @return string
     */
    public function getElementDetailsId()
    {
        return $this->elementDetailsId;
    }

    /**
     * @param string $elementDetailsId
     */
    public function setElementDetailsId($elementDetailsId)
    {
        $this->elementDetailsId = $elementDetailsId;
    }


    function get_months($date1, $date2)
    {
        $time1  = strtotime($date1);
        $time2  = strtotime($date2);
        $my     = date('mY', $time2);

        $months = array(date('F', $time1));

        while($time1 < $time2) {
            $time1 = strtotime(date('Y-m-d', $time1).' +1 month');
            if(date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('F', $time1);
        }

        $months[] = date('F', $time2);
        return $months;
    }

    function get_months_and_year($date1, $date2) {
        $time1  = strtotime($date1);
        $time2  = strtotime($date2);
        $my     = date('mY', $time2);

        $monthsyear = array(date('Y-m-d', $time1));

        while($time1 < $time2) {
            $time1 = strtotime(date('Y-m-d', $time1).' +1 month');
            if(date('mY', $time1) != $my && ($time1 < $time2))
                $monthsyear[] = date('Y-m-d', $time1);
        }

        $monthsyear[] = date('Y-m-d', $time2);
        return $monthsyear;
    }

}
