<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipTypes
 *
 * @ORM\Table(name="ship_types")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ShipTypesRepository")
 */
class ShipTypes
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
     * @ORM\Column(name="ShipType", type="string", length=255)
     */
    private $shipType;


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
     * Set shipType
     *
     * @param string $shipType
     * @return ShipTypes
     */
    public function setShipType($shipType)
    {
        $this->shipType = $shipType;

        return $this;
    }

    /**
     * Get shipType
     *
     * @return string 
     */
    public function getShipType()
    {
        return $this->shipType;
    }

    public function __toString()
    {
        return $this->getShipType() ? $this->getShipType() : "";
    }
}
