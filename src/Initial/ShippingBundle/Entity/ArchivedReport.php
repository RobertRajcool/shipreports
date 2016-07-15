<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArchivedReport
 *
 * @ORM\Table(name="archived_report")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ArchivedReportRepository")
 */
class ArchivedReport
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
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="userId", type="string", length=255)
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     */
    private $dateTime;


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
     * Set fileName
     *
     * @param string $fileName
     *
     * @return ArchivedReport
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set userId
     *
     * @param string $userId
     *
     * @return ArchivedReport
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set dateTime
     *
     * @param \DateTime $dateTime
     *
     * @return ArchivedReport
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}

