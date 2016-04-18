<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SendCommandRanking
 *
 * @ORM\Table(name="send_command_ranking")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\SendCommandRankingRepository")
 */
class SendCommandRanking
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
     * @ORM\Column(name="comment", type="string", length=35)
     */
    private $comment;
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string",length=35, nullable=true)
     */
    private $filename;
    /**
     * @var string
     *
     * @ORM\Column(name="clientemail", type="string", length=35,nullable=true)
     */
    private $clientemail;
    /**
     * @var string
     *
     * @ORM\Column(name="useremialid", type="string", length=35,nullable=true)
     */
    private $useremialid;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime",nullable=true)
     */
    private $datetime;

    /**
     * @var int
     *
     * @ORM\Column(name="kpiid", type="integer",nullable=true)
     */
    private $kpiid;
    /**
     * @var int
     *
     * @ORM\Column(name="shipid", type="integer",nullable=true)
     */
    private $shipid;



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
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return int
     */
    public function getShipid()
    {
        return $this->shipid;
    }

    /**
     * @param int $shipid
     */
    public function setShipid($shipid)
    {
        $this->shipid = $shipid;
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
     * @return int
     */
    public function getKpiid()
    {
        return $this->kpiid;
    }

    /**
     * @param int $kpiid
     */
    public function setKpiid($kpiid)
    {
        $this->kpiid = $kpiid;
    }

    /**
     * @return string
     */
    public function getUseremialid()
    {
        return $this->useremialid;
    }

    /**
     * @param string $useremialid
     */
    public function setUseremialid($useremialid)
    {
        $this->useremialid = $useremialid;
    }

    /**
     * @return string
     */
    public function getClientemail()
    {
        return $this->clientemail;
    }

    /**
     * @param string $clientemail
     */
    public function setClientemail($clientemail)
    {
        $this->clientemail = $clientemail;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

}
