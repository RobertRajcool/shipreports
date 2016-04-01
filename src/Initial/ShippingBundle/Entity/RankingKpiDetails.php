<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RankingKpiDetails
 *
 * @ORM\Table(name="ranking_kpi_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\RankingKpiDetailsRepository")
 */
class RankingKpiDetails
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
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ShipDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ShipDetailsId", referencedColumnName="id")
     * })
     */
    private $shipDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="KpiName", type="string", length=255)
     */
    private $kpiName;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=255)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ActiveDate", type="date")
     */
    private $activeDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndDate", type="date")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="CellName", type="string", length=255)
     */
    private $cellName;

    /**
     * @var string
     *
     * @ORM\Column(name="CellDetails", type="string", length=255)
     */
    private $cellDetails;

    /**
     * @var string
     *
     * @ORM\Column(name="Weightage", type="string", length=255)
     */
    private $weightage;


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
     * Set shipDetailsId
     *
     * @param string $shipDetailsId
     * @return RankingKpiDetails
     */
    public function setShipDetailsId($shipDetailsId)
    {
        $this->shipDetailsId = $shipDetailsId;

        return $this;
    }

    /**
     * Get shipDetailsId
     *
     * @return string 
     */
    public function getShipDetailsId()
    {
        return $this->shipDetailsId;
    }

    /**
     * Set kpiName
     *
     * @param string $kpiName
     * @return RankingKpiDetails
     */
    public function setKpiName($kpiName)
    {
        $this->kpiName = $kpiName;

        return $this;
    }
    public function __toString()
    {
        return $this->getKpiName() ? $this->getKpiName() : "";
    }

    /**
     * Get kpiName
     *
     * @return string 
     */
    public function getKpiName()
    {
        return $this->kpiName;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RankingKpiDetails
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set activeDate
     *
     * @param \DateTime $activeDate
     * @return RankingKpiDetails
     */
    public function setActiveDate($activeDate)
    {
        $this->activeDate = $activeDate;

        return $this;
    }

    /**
     * Get activeDate
     *
     * @return \DateTime 
     */
    public function getActiveDate()
    {
        return $this->activeDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return RankingKpiDetails
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set cellName
     *
     * @param string $cellName
     * @return RankingKpiDetails
     */
    public function setCellName($cellName)
    {
        $this->cellName = $cellName;

        return $this;
    }

    /**
     * Get cellName
     *
     * @return string 
     */
    public function getCellName()
    {
        return $this->cellName;
    }

    /**
     * Set cellDetails
     *
     * @param string $cellDetails
     * @return RankingKpiDetails
     */
    public function setCellDetails($cellDetails)
    {
        $this->cellDetails = $cellDetails;

        return $this;
    }

    /**
     * Get cellDetails
     *
     * @return string 
     */
    public function getCellDetails()
    {
        return $this->cellDetails;
    }

    /**
     * Set weightage
     *
     * @param string $weightage
     * @return RankingKpiDetails
     */
    public function setWeightage($weightage)
    {
        $this->weightage = $weightage;

        return $this;
    }

    /**
     * Get weightage
     *
     * @return string 
     */
    public function getWeightage()
    {
        return $this->weightage;
    }
}
