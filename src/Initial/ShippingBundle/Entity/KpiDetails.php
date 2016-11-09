<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * KpiDetails
 *
 * @ORM\Table(name="kpi_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\KpiDetailsRepository")
 */
class KpiDetails
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
     * @ORM\Column(name="KpiName", type="string", length=125)
     */
    private $kpiName;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=150, nullable=true)
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
     * @ORM\Column(name="CellName", type="string", length=35, nullable=true)
     */
    private $cellName;

    /**
     * @var string
     *
     * @ORM\Column(name="CellDetails", type="string", length=75, nullable=true)
     */
    private $cellDetails;

    /**
     * @var string
     *
     * @ORM\Column(name="Weightage", type="integer", length=30)
     */
    private $weightage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     */
    private $dateTime;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $userId;

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
     * @return KpiDetails
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
     * @return KpiDetails
     */
    public function setKpiName($kpiName)
    {
        $this->kpiName = $kpiName;

        return $this;
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
    public function __toString()
    {
        return $this->getKpiName() ? $this->getKpiName() : "";
    }
    /**
     * Set description
     *
     * @param string $description
     * @return KpiDetails
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
     * @return KpiDetails
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
     * @return KpiDetails
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
     * @return KpiDetails
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
     * @return KpiDetails
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
     * @return string
     */
    public function getWeightage()
    {
        return $this->weightage;
    }

    /**
     * @param string $weightage
     */
    public function setWeightage($weightage)
    {
        $this->weightage = $weightage;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


}