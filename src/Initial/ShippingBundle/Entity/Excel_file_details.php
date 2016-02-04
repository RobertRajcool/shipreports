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






}
