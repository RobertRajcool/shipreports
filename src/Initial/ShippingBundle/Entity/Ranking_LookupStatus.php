<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ranking_LookupStatus
 *
 * @ORM\Table(name="ranking__lookup_status")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\Ranking_LookupStatusRepository")
 */
class Ranking_LookupStatus
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
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ShipDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipid", referencedColumnName="id")
     * })
     */
    private $shipid;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="rejections", type="string", nullable=true)
     */
    private $rejections;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dataofmonth", type="date")
     */
    private $dataofmonth;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime")
     */
    private $datetime;
    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="string", length=255)
     */
    private $userid;


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
     * @return string
     */
    public function getShipid()
    {
        return $this->shipid;
    }

    /**
     * @param string $shipid
     */
    public function setShipid($shipid)
    {
        $this->shipid = $shipid;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getDataofmonth()
    {
        return $this->dataofmonth;
    }

    /**
     * @param \DateTime $dataofmonth
     */
    public function setDataofmonth($dataofmonth)
    {
        $this->dataofmonth = $dataofmonth;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param \DateTime $datetime
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * @return string
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * @param string $userid
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
    }

    /**
     * @return string
     */
    public function getRejections()
    {
        return $this->rejections;
    }

    /**
     * @param string $rejections
     */
    public function setRejections($rejections)
    {
        $this->rejections = $rejections;
    }


}
