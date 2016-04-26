<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scorecard_LookupData
 *
 * @ORM\Table(name="scorecard__lookup_data")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\Scorecard_LookupDataRepository")
 */
class Scorecard_LookupData
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
     * @ORM\Column(name="individualKpiAverageScore", type="string", length=255)
     */

    private $individualKpiAverageScore;

    /**
     * @var string
     *
     * @ORM\Column(name="elementcolor", type="string", length=255)
     */
    private $elementcolor;
    /**
     * @var string
     *
     * @ORM\Column(name="kpiColor", type="string", length=255)
     */
    private $kpiColor;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="monthdetail", type="date")
     */
    private $monthdetail;

    /**
     * @var string
     *
     * @ORM\Column(name="shipDetailsId", type="string", length=255)
     */
    private $shipDetailsId;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\KpiDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="KpiDetailsId", referencedColumnName="id")
     * })
     */
    private $kpiDetailsId;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\ElementDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ElementDetailsId", referencedColumnName="id")
     * })
     */
    private $elementDetailsId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getIndividualKpiAverageScore()
    {
        return $this->individualKpiAverageScore;
    }

    /**
     * @param string $individualKpiAverageScore
     */
    public function setIndividualKpiAverageScore($individualKpiAverageScore)
    {
        $this->individualKpiAverageScore = $individualKpiAverageScore;
    }

    /**
     * @return string
     */
    public function getElementcolor()
    {
        return $this->elementcolor;
    }

    /**
     * @param string $elementcolor
     */
    public function setElementcolor($elementcolor)
    {
        $this->elementcolor = $elementcolor;
    }

    /**
     * @return string
     */
    public function getKpiColor()
    {
        return $this->kpiColor;
    }

    /**
     * @param string $kpiColor
     */
    public function setKpiColor($kpiColor)
    {
        $this->kpiColor = $kpiColor;
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
