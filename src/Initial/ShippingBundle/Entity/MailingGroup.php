<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MailingGroup
 *
 * @ORM\Table(name="mailing_group")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\MailingGroupRepository")
 */
class MailingGroup
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
     * @ORM\Column(name="groupname", type="string", length=255)
     */
    private $groupname;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\Mailing")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="emailreferenceid", referencedColumnName="id")
     * })
     */
    private $emailreferenceid;


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
     * Set groupname
     *
     * @param string $groupname
     * @return MailingGroup
     */
    public function setGroupname($groupname)
    {
        $this->groupname = $groupname;

        return $this;
    }

    /**
     * Get groupname
     *
     * @return string 
     */
    public function getGroupname()
    {
        return $this->groupname;
    }

    /**
     * @return string
     */
    public function getEmailreferenceid()
    {
        return $this->emailreferenceid;
    }

    /**
     * @param string $emailreferenceid
     */
    public function setEmailreferenceid($emailreferenceid)
    {
        $this->emailreferenceid = $emailreferenceid;
    }


}
