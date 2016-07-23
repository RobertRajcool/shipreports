<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementSymbols
 *
 * @ORM\Table(name="element_symbols")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ElementSymbolsRepository")
 */
class ElementSymbols
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
     * @ORM\Column(name="symbolName", type="string", length=255)
     */
    private $symbolName;

    /**
     * @var string
     *
     * @ORM\Column(name="symbolIndication", type="string", length=255)
     */
    private $symbolIndication;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set symbolName
     *
     * @param string $symbolName
     *
     * @return ElementSymbols
     */
    public function setSymbolName($symbolName)
    {
        $this->symbolName = $symbolName;

        return $this;
    }

    /**
     * Get symbolName
     *
     * @return string
     */
    public function getSymbolName()
    {
        return $this->symbolName;
    }

    /**
     * Set symbolIndication
     *
     * @param string $symbolIndication
     *
     * @return ElementSymbols
     */
    public function setSymbolIndication($symbolIndication)
    {
        $this->symbolIndication = $symbolIndication;

        return $this;
    }

    /**
     * Get symbolIndication
     *
     * @return string
     */
    public function getSymbolIndication()
    {
        return $this->symbolIndication;
    }

}

