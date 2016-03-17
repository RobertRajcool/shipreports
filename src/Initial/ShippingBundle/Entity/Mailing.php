<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mailing
 *
 * @ORM\Table(name="mailing")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\MailingRepository")
 */
class Mailing
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
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="emailid", type="string", length=255)
     */
    private $emailid;
    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\CompanyDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyid", referencedColumnName="id")
     * })
     */
    private $companyid;


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
     * Set username
     *
     * @param string $username
     * @return Mailing
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set emailid
     *
     * @param string $emailid
     * @return Mailing
     */
    public function setEmailid($emailid)
    {
        $this->emailid = $emailid;

        return $this;
    }

    /**
     * Get emailid
     *
     * @return string 
     */
    public function getEmailid()
    {
        return $this->emailid;
    }

    /**
     * @return string
     */
    public function getCompanyid()
    {
        return $this->companyid;
    }

    /**
     * @param string $companyid
     */
    public function setCompanyid($companyid)
    {
        $this->companyid = $companyid;
    }

}
