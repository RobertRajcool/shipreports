<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RankingElementDetails
 *
 * @ORM\Table(name="ranking_element_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\RankingElementDetailsRepository")
 */
class RankingElementDetails
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
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\RankingKpiDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="KpiDetailsId", referencedColumnName="id")
     * })
     */
    private $kpiDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="ElementName", type="string", length=255)
     */
    private $elementName;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=255)
     */
    private $description;

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
     * @ORM\Column(name="Weightage", type="string", length=255)
     */
    private $weightage;

    /**
     * @var string
     *
     * @ORM\Column(name="Rules", type="string", length=255)
     */
    private $rules;

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
     * @return RankingElementDetails
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
     * @return RankingElementDetails
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
     * @return RankingElementDetails
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
     * @return RankingElementDetails
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
     * @return RankingElementDetails
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
     * Set activeDate
     *
     * @param \DateTime $activeDate
     * @return RankingElementDetails
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
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
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


}