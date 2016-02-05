<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyUsers
 *
 * @ORM\Table(name="company_users")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\CompanyUsersRepository")
 */
class CompanyUsers
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
     * @var integer
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\CompanyDetails", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyName", referencedColumnName="id")
     * })
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="UserName", type="string", length=125)
     */
    private $userName;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\UserRole", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role", referencedColumnName="id")
     * })
     */
    private $role;

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
     * @return CompanyUsers
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

    /**
     * Set userName
     *
     * @param string $userName
     * @return CompanyUsers
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }
    public function __toString()
    {
        return $this->getUserName() ? $this->getUserName() : "";
    }
    /**
     * Set role
     *
     * @param string $role
     * @return CompanyUsers
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set emailId
     *
     * @param string $emailId
     * @return CompanyUsers
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
