<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppsCountries
 *
 * @ORM\Table(name="apps_countries")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\AppsCountriesRepository")
 */
class AppsCountries
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
     * @ORM\Column(name="CountryCode", type="string", length=4)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="CountryName", type="string", length=255)
     */
    private $countryName;


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
     * Set countryCode
     *
     * @param string $countryCode
     * @return AppsCountries
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set countryName
     *
     * @param string $countryName
     * @return AppsCountries
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * Get countryName
     *
     * @return string 
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    public function __toString()
    {
        return $this->getCountryName() ? $this->getCountryName() : "";
    }
}
