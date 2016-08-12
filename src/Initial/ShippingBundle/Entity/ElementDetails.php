<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementDetails
 *
 * @ORM\Table(name="element_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ElementDetailsRepository")
 */
class ElementDetails
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
     *   @ORM\JoinColumn(name="KpiDetailsId", referencedColumnName="id")
     * })
     */
    private $kpiDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="ElementName", type="string", length=125)
     */
    private $elementName;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=75, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="CellName", type="string", length=35)
     */
    private $cellName;

    /**
     * @var string
     *
     * @ORM\Column(name="CellDetails", type="string", length=75, nullable=true)
     */
    private $cellDetails;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ActivatedDate", type="date")
     */
    private $activatedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndDate", type="date")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="Weightage", type="integer", length=50)
     */
    private $weightage;

    /**
     * @var string
     *
     * @ORM\Column(name="Rules", type="string", length=255, nullable=true)
     */
    private $rules;

    /**
     * @var string
     *
     * @ORM\Column(name="VesselWiseTotal", type="string", length=35)
     */
    private $vesselWiseTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="IndicationValue", type="string", length=50)
     */
    private $indicationValue;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ElementSymbols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="SymbolId", referencedColumnName="id")
     * })
     */
    private $symbolId;

    /**
     * @var string
     *
     * @ORM\Column(name="ComparisonStatus", type="string", length=35)
     */
    private $comparisonStatus;


    /**
     * @var string
     *
     * @ORM\Column(name="BaseValue", type="integer", length=11)
     */
    private $baseValue;


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
     * Set kpiDetailsId
     *
     * @param string $kpiDetailsId
     * @return ElementDetails
     */
    public function setKpiDetailsId($kpiDetailsId)
    {
        $this->kpiDetailsId = $kpiDetailsId;

        return $this;
    }

    /**
     * Get kpiDetailsId
     *
     * @return string 
     */
    public function getKpiDetailsId()
    {
        return $this->kpiDetailsId;
    }

    /**
     * Set elementName
     *
     * @param string $elementName
     * @return ElementDetails
     */
    public function setElementName($elementName)
    {
        $this->elementName = $elementName;

        return $this;
    }

    /**
     * Get elementName
     *
     * @return string 
     */
    public function getElementName()
    {
        return $this->elementName;
    }

    public function __toString()
    {
        return $this->getElementName() ? $this->getElementName() : "";
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ElementDetails
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
     * Set cellName
     *
     * @param string $cellName
     * @return ElementDetails
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
     * @return ElementDetails
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
     * Set activatedDate
     *
     * @param \DateTime $activatedDate
     * @return ElementDetails
     */
    public function setActivatedDate($activatedDate)
    {
        $this->activatedDate = $activatedDate;

        return $this;
    }

    /**
     * Get activatedDate
     *
     * @return \DateTime 
     */
    public function getActivatedDate()
    {
        return $this->activatedDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return ElementDetails
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
     * Set weightage
     *
     * @param string $weightage
     * @return ElementDetails
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


    /**
     * Get rules
     *
     * @return string
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Set rules
     *
     * @param string $rules
     * @return ElementDetails
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return string
     */
    public function getVesselWiseTotal()
    {
        return $this->vesselWiseTotal;
    }

    /**
     * @param string $vesselWiseTotal
     */
    public function setVesselWiseTotal($vesselWiseTotal)
    {
        $this->vesselWiseTotal = $vesselWiseTotal;
    }

    /**
     * @return string
     */
    public function getIndicationValue()
    {
        return $this->indicationValue;
    }

    /**
     * @param string $indicationValue
     */
    public function setIndicationValue($indicationValue)
    {
        $this->indicationValue = $indicationValue;
    }

    /**
     * @return string
     */
    public function getSymbolId()
    {
        return $this->symbolId;
    }

    /**
     * @param string $symbolId
     */
    public function setSymbolId($symbolId)
    {
        $this->symbolId = $symbolId;
    }

    /**
     * @return string
     */
    public function getComparisonStatus()
    {
        return $this->comparisonStatus;
    }

    /**
     * @param string $comparisonStatus
     */
    public function setComparisonStatus($comparisonStatus)
    {
        $this->comparisonStatus = $comparisonStatus;
    }
    /**
     * @return string
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * @param string $baseValue
     */
    public function setBaseValue($baseValue)
    {
        $this->baseValue = $baseValue;
    }

}
