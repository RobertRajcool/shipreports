<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogDetails
 *
 * @ORM\Table(name="log_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\LogDetailsRepository")
 */
class LogDetails
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var integer
     *
     * @ORM\Column(name="CreatedByID", type="integer", nullable=false)
     */
    private $createdbyid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="CreatedOnDateTime", type="datetime", nullable=false)
     */
    private $createdondatetime;

    /**
     * @var string
     *
     * @ORM\Column(name="TableName", type="string", nullable=false)
     */
    private $tablename;

    /**
     * @var integer
     *
     * @ORM\Column(name="TablePKID", type="integer", nullable=false)
     */
    private $tablepkid;

    /**
     * @var string
     *
     * @ORM\Column(name="oldvalue", type="string", nullable=false)
     */
    private $oldvalue;
    /**
     * @var string
     *
     * @ORM\Column(name="fieldName", type="string", nullable=false)
     */
    private $fieldName;


    /**
     * @var string
     *
     * @ORM\Column(name="newvalue", type="string", nullable=false)
     */
    private $newvalue;


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
     * @return int
     */
    public function getCreatedbyid()
    {
        return $this->createdbyid;
    }

    /**
     * @param int $createdbyid
     */
    public function setCreatedbyid($createdbyid)
    {
        $this->createdbyid = $createdbyid;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedondatetime()
    {
        return $this->createdondatetime;
    }

    /**
     * @param \DateTime $createdondatetime
     */
    public function setCreatedondatetime($createdondatetime)
    {
        $this->createdondatetime = $createdondatetime;
    }

    /**
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * @param string $tablename
     */
    public function setTablename($tablename)
    {
        $this->tablename = $tablename;
    }


    /**
     * @return int
     */
    public function getTablepkid()
    {
        return $this->tablepkid;
    }

    /**
     * @param int $tablepkid
     */
    public function setTablepkid($tablepkid)
    {
        $this->tablepkid = $tablepkid;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getNewvalue()
    {
        return $this->newvalue;
    }

    /**
     * @param string $newvalue
     */
    public function setNewvalue($newvalue)
    {
        $this->newvalue = $newvalue;
    }

    /**
     * @return string
     */
    public function getOldvalue()
    {
        return $this->oldvalue;
    }

    /**
     * @param string $oldvalue
     */
    public function setOldvalue($oldvalue)
    {
        $this->oldvalue = $oldvalue;
    }




}

