<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyDetails
 *
 * @ORM\Table(name="company_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\CompanyDetailsRepository")
 */
class CompanyDetails
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
     * @ORM\Column(name="CompanyName", type="string", length=125, unique=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="AdminName", type="string", length=75)
     */
    private $adminName;

    /**
     * @var string
     *
     * @ORM\Column(name="EmailId", type="string", length=75, unique=true)
     */
    private $emailId;


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
     * Set companyName
     *
     * @param string $companyName
     * @return CompanyDetails
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string 
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }
    public function __toString()
    {
        return $this->getCompanyName() ? $this->getCompanyName() : "";
    }
    /**
     * Set adminName
     *
     * @param string $adminName
     * @return CompanyDetails
     */
    public function setAdminName($adminName)
    {
        $this->adminName = $adminName;

        return $this;
    }

    /**
     * Get adminName
     *
     * @return string 
     */
    public function getAdminName()
    {
        return $this->adminName;
    }

    /**
     * Set emailId
     *
     * @param string $emailId
     * @return CompanyDetails
     */
    public function setEmailId($emailId)
    {
        $this->emailId = $emailId;

        return $this;
    }

    /**
     * Get emailId
     *
     * @return string 
     */
    public function getEmailId()
    {
        return $this->emailId;
    }
}
