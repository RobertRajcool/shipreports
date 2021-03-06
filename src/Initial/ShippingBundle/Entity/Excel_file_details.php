<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Excel_file_details
 *
 * @ORM\Table(name="excel_file_details")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\Excel_file_detailsRepository")
 */
class Excel_file_details
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
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_of_month", type="date")
     */
    private $dataOfMonth;
    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="string", length=255)
     */
    private $userid;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime")
     */
    private $datetime;
    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $company_id;
    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\RankingFolder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folderId", referencedColumnName="id")
     * })
     */
    private $folderId;
    /**
     * @var integer
     *
     * @ORM\Column(name="kpiDetailsId", type="integer",  nullable=true)
     */
    private $kpiDetailsId;

    /**
     * @var integer
     *
     * @ORM\Column(name="elementDetailsId", type="integer",  nullable=true)
     */
    private $elementDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="vesselId", type="string",  nullable=true)
     */
    private $vesselId;
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }


    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set dataOfMonth
     *
     * @param \DateTime $dataOfMonth
     * @return Excel_file_details
     */
    public function setDataOfMonth($dataOfMonth)
    {
        $this->dataOfMonth = $dataOfMonth;

        return $this;
    }

    /**
     * Get dataOfMonth
     *
     * @return \DateTime
     */
    public function getDataOfMonth()
    {
        return $this->dataOfMonth;
    }




    public function extract_numbers($string)
    {
        preg_match_all('/([\d]+)/', $string, $match);

        return $match[0];
    }
    public function removeUpload($filename)
    {


        unlink($filename);
        return true;

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
    public function getCompanyId()
    {
        return $this->company_id;
    }

    /**
     * @param int $company_id
     */
    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;
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

    /**
     * @return int
     */
    public function getKpiDetailsId()
    {
        return $this->kpiDetailsId;
    }

    /**
     * @param int $kpiDetailsId
     */
    public function setKpiDetailsId($kpiDetailsId)
    {
        $this->kpiDetailsId = $kpiDetailsId;
    }

    /**
     * @return string
     */
    public function getVesselId()
    {
        return $this->vesselId;
    }

    /**
     * @param string $vesselId
     */
    public function setVesselId($vesselId)
    {
        $this->vesselId = $vesselId;
    }

    /**
     * @return int
     */
    public function getElementDetailsId()
    {
        return $this->elementDetailsId;
    }

    /**
     * @param int $elementDetailsId
     */
    public function setElementDetailsId($elementDetailsId)
    {
        $this->elementDetailsId = $elementDetailsId;
    }


}
