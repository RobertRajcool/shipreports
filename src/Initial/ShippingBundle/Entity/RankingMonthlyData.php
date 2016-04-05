<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RankingMonthlyData
 *
 * @ORM\Table(name="ranking_monthly_data")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\RankingMonthlyDataRepository")
 */
class RankingMonthlyData
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
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="monthdetail", type="date")
     */
    private $monthdetail;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ShipDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ShipDetailsId", referencedColumnName="id")
     * })
     */
    private $shipDetailsId;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\RankingKpiDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="KpiDetailsId", referencedColumnName="id")
     * })
     */
    private $kpiDetailsId;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\RankingElementDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ElementDetailsId", referencedColumnName="id")
     * })
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
     * Set value
     *
     * @param string $value
     * @return RankingMonthlyData
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set monthdetail
     *
     * @param \DateTime $monthdetail
     * @return RankingMonthlyData
     */
    public function setMonthdetail($monthdetail)
    {
        $this->monthdetail = $monthdetail;

        return $this;
    }

    /**
     * Get monthdetail
     *
     * @return \DateTime
     */
    public function getMonthdetail()
    {
        return $this->monthdetail;
    }

    /**
     * Set shipDetailsId
     *
     * @param integer $shipDetailsId
     * @return RankingMonthlyData
     */
    public function setShipDetailsId($shipDetailsId)
    {
        $this->shipDetailsId = $shipDetailsId;

        return $this;
    }

    /**
     * Get shipDetailsId
     *
     * @return integer
     */
    public function getShipDetailsId()
    {
        return $this->shipDetailsId;
    }

    /**
     * Set kpiDetailsId
     *
     * @param integer $kpiDetailsId
     * @return RankingMonthlyData
     */
    public function setKpiDetailsId($kpiDetailsId)
    {
        $this->kpiDetailsId = $kpiDetailsId;

        return $this;
    }

    /**
     * Get kpiDetailsId
     *
     * @return integer
     */
    public function getKpiDetailsId()
    {
        return $this->kpiDetailsId;
    }

    /**
     * Set elementDetailsId
     *
     * @param integer $elementDetailsId
     * @return RankingMonthlyData
     */
    public function setElementDetailsId($elementDetailsId)
    {
        $this->elementDetailsId = $elementDetailsId;

        return $this;
    }

    /**
     * Get elementDetailsId
     *
     * @return integer
     */
    public function getElementDetailsId()
    {
        return $this->elementDetailsId;
    }

}
