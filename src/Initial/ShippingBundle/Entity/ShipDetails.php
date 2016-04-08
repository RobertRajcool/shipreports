<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipDetails
 *
 * @ORM\Table(name="ship_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ShipDetailsRepository")
 */
class ShipDetails
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
     * @ORM\Column(name="ShipName", type="string", length=100, unique=true)
     */
    private $shipName;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ShipTypes", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipType", referencedColumnName="id")
     * })
     */
    private $shipType;

    /**
     * @var string
     *
     * @ORM\Column(name="imoNumber", type="string", length=125)
     */
    private $imoNumber;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\AppsCountries", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country", referencedColumnName="id")
     * })
     */
    private $country;


    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\CompanyDetails", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyDetailsId", referencedColumnName="id")
     * })
     */
    private $companyDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=125, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=125)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="manufacturingYear", type="string", length=125)
     */
    private $manufacturingYear;



    /**
     * @var string
     *
     * @ORM\Column(name="built", type="string", length=125)
     */
    private $built;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=125)
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="gt", type="string", length=125)
     */
    private $gt;


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
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Set shipName
     *
     * @param string $shipName
     * @return ShipDetails
     */
    public function setShipName($shipName)
    {
        $this->shipName = $shipName;

        return $this;
    }

    /**
     * Get shipName
     *
     * @return string 
     */
    public function getShipName()
    {
        return $this->shipName;
    }
    public function __toString()
    {
        return $this->getShipName() ? $this->getShipName() : "";
    }

    /**
     * @return string
     */
    public function getShipType()
    {
        return $this->shipType;
    }

    /**
     * @param string $shipType
     */
    public function setShipType($shipType)
    {
        $this->shipType = $shipType;
    }

    /**
     * @return string
     */
    public function getImoNumber()
    {
        return $this->imoNumber;
    }

    /**
     * @param string $imoNumber
     */
    public function setImoNumber($imoNumber)
    {
        $this->imoNumber = $imoNumber;
    }

    /**
     * Set companyDetailsId
     *
     * @param string $companyDetailsId
     * @return ShipDetails
     */
    public function setCompanyDetailsId($companyDetailsId)
    {
        $this->companyDetailsId = $companyDetailsId;

        return $this;
    }

    /**
     * Get companyDetailsId
     *
     * @return string
     */
    public function getCompanyDetailsId()
    {
        return $this->companyDetailsId;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    /**
     * Set description
     *
     * @param string $description
     * @return ShipDetails
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
     * @return string
     */
    public function getManufacturingYear()
    {
        return $this->manufacturingYear;
    }

    /**
     * @param string $manufacturingYear
     */
    public function setManufacturingYear($manufacturingYear)
    {
        $this->manufacturingYear = $manufacturingYear;
    }

    /**
     * @return string
     */
    public function getBuilt()
    {
        return $this->built;
    }

    /**
     * @param string $built
     */
    public function setBuilt($built)
    {
        $this->built = $built;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getGt()
    {
        return $this->gt;
    }

    /**
     * @param string $gt
     */
    public function setGt($gt)
    {
        $this->gt = $gt;
    }


}
