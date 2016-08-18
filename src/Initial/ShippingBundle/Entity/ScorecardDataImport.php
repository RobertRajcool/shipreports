<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScorecardDataImport
 *
 * @ORM\Table(name="scorecard_data_import")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ScorecardDataImportRepository")
 */
class ScorecardDataImport
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
     * @var \DateTime
     *
     * @ORM\Column(name="monthDetail", type="date")
     */
    private $monthDetail;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTime", type="datetime")
     */
    private $dateTime;
    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ScorecardFolder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folderId", referencedColumnName="id")
     * })
     */
    private $folderId;


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
     * @return ScorecardDataImport
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
     * Set monthDetail
     *
     * @param \DateTime $monthDetail
     *
     * @return ScorecardDataImport
     */
    public function setMonthDetail($monthDetail)
    {
        $this->monthDetail = $monthDetail;

        return $this;
    }

    /**
     * Get monthDetail
     *
     * @return \DateTime
     */
    public function getMonthDetail()
    {
        return $this->monthDetail;
    }

    /**
     * Set userId
     *
     * @param string $userId
     *
     * @return ScorecardDataImport
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
     * @return ScorecardDataImport
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

    /**
     * @return string
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * @param string $folderId
     */
    public function setFolderId($folderId)
    {
        $this->folderId = $folderId;
    }

}

