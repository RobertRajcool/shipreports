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
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\CompanyDetails", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyDetailsId", referencedColumnName="id")
     * })
     */
    private $companyDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=125)
     */
    private $description;


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
}
