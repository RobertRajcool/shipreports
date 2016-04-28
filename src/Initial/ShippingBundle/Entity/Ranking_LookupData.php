<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ranking_LookupData
 *
 * @ORM\Table(name="ranking__lookup_data")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\Ranking_LookupDataRepository")
 */
class Ranking_LookupData
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
     * @ORM\Column(name="elementdata", type="string", length=255)
     */

    private $elementdata;

    /**
     * @var string
     *
     * @ORM\Column(name="elementcolor", type="string", length=255)
     */
    private $elementcolor;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="monthdetail", type="date")
     */
    private $monthdetail;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ShipDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ShipDetailsId", referencedColumnName="id")
     * })
     */
    private $shipDetailsId;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\RankingKpiDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="KpiDetailsId", referencedColumnName="id")
     * })
     */
    private $kpiDetailsId;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\RankingElementDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ElementDetailsId", referencedColumnName="id")
     * })
     */
    private $elementDetailsId;


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
     * Set elementdata
     *
     * @param string $elementdata
     * @return Ranking_LookupData
     */
    public function setElementdata($elementdata)
    {
        $this->elementdata = $elementdata;

        return $this;
    }

    /**
     * Get elementdata
     *
     * @return string 
     */
    public function getElementdata()
    {
        return $this->elementdata;
    }

    /**
     * Set elementcolor
     *
     * @param string $elementcolor
     * @return Ranking_LookupData
     */
    public function setElementcolor($elementcolor)
    {
        $this->elementcolor = $elementcolor;

        return $this;
    }

    /**
     * Get elementcolor
     *
     * @return string 
     */
    public function getElementcolor()
    {
        return $this->elementcolor;
    }

    /**
     * @return \DateTime
     */
    public function getMonthdetail()
    {
        return $this->monthdetail;
    }

    /**
     * @param \DateTime $monthdetail
     */
    public function setMonthdetail($monthdetail)
    {
        $this->monthdetail = $monthdetail;
    }

    /**
     * @return string
     */
    public function getShipDetailsId()
    {
        return $this->shipDetailsId;
    }

    /**
     * @param string $shipDetailsId
     */
    public function setShipDetailsId($shipDetailsId)
    {
        $this->shipDetailsId = $shipDetailsId;
    }

    /**
     * @return string
     */
    public function getKpiDetailsId()
    {
        return $this->kpiDetailsId;
    }

    /**
     * @param string $kpiDetailsId
     */
    public function setKpiDetailsId($kpiDetailsId)
    {
        $this->kpiDetailsId = $kpiDetailsId;
    }

    /**
     * @return string
     */
    public function getElementDetailsId()
    {
        return $this->elementDetailsId;
    }

    /**
     * @param string $elementDetailsId
     */
    public function setElementDetailsId($elementDetailsId)
    {
        $this->elementDetailsId = $elementDetailsId;
    }

}
