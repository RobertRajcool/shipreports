<?php
// src/AppBundle/Entity/User.php

namespace Initial\ShippingBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\CompanyDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyid", referencedColumnName="id")
     * })
     */
    protected $companyid;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}

?>