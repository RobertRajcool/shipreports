<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubElementDetails
 *
 * @ORM\Table(name="sub_element_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\SubElementDetailsRepository")
 */
class SubElementDetails
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
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ElementDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ElementDetailsId", referencedColumnName="id")
     * })
     */
    private $elementDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="SubElementName", type="string", length=120)
     */
    private $subElementName;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=120)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="CellName", type="string", length=30)
     */
    private $cellName;

    /**
     * @var string
     *
     * @ORM\Column(name="CellDetails", type="string", length=120)
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
     * @ORM\Column(name="Weightage", type="string", length=50)
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
     * Set elementDetailsId
     *
     * @param string $elementDetailsId
     * @return SubElementDetails
     */
    public function setElementDetailsId($elementDetailsId)
    {
        $this->elementDetailsId = $elementDetailsId;

        return $this;
    }

    /**
     * Get elementDetailsId
     *
     * @return string 
     */
    public function getElementDetailsId()
    {
        return $this->elementDetailsId;
    }

    /**
     * Set subElementName
     *
     * @param string $subElementName
     * @return SubElementDetails
     */
    public function setSubElementName($subElementName)
    {
        $this->subElementName = $subElementName;

        return $this;
    }

    /**
     * Get subElementName
     *
     * @return string 
     */
    public function getSubElementName()
    {
        return $this->subElementName;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return SubElementDetails
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
     * @return SubElementDetails
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
     * @return SubElementDetails
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
     * @return SubElementDetails
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
     * @return SubElementDetails
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
     * @return SubElementDetails
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
