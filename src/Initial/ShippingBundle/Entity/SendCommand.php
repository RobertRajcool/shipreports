<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * SendCommand
 *
 * @ORM\Table(name="send_command")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\SendCommandRepository")
 */
class SendCommand
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
     * @ORM\Column(name="filename", type="string", length=35)
     */
    private $filename;
    /**
     * @var string
     *
     * @ORM\Column(name="clientemail", type="string", length=35)
     */
    private $clientemail;
    /**
     * @var string
     *
     * @ORM\Column(name="useremialid", type="string", length=35)
     */
    private $useremialid;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="date")
     */
    private $datetime;


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
     * @return \DateTime
     */
    public function getDatetime()
    {

        return $this->datetime=new \DateTime();
    }

    /**
     * @param \DateTime $datetime
     */
    public function setDatetime($datetime)
    {
        $this->datetime = new \DateTime();;
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


}
