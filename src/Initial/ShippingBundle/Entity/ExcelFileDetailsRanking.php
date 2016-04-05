<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExcelFileDetailsRanking
 *
 * @ORM\Table(name="excel_file_details_ranking")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ExcelFileDetailsRankingRepository")
 */
class ExcelFileDetailsRanking
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
     * @return \DateTime
     */
    public function getDataOfMonth()
    {
        return $this->dataOfMonth;
    }

    /**
     * @param \DateTime $dataOfMonth
     */
    public function setDataOfMonth($dataOfMonth)
    {
        $this->dataOfMonth = $dataOfMonth;
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


}
