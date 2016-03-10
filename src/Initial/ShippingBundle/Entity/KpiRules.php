<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* KpiRules
*
* @ORM\Table(name="kpi_rules")
* @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\KpiRulesRepository")
*/
class KpiRules
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
* @ORM\Column(name="Rules", type="string", length=255)
*/
private $rules;

/**
* @var string
* @ORM\ManyToOne(targetEntity="Initial\ShippingBundle\Entity\KpiDetails")
* @ORM\JoinColumns({
*   @ORM\JoinColumn(name="KpiDetailsId", referencedColumnName="id")
* })
*/
private $kpiDetailsId;


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
* Set rules
*
* @param string $rules
*
* @return KpiRules
*/
public function setRules($rules)
{
$this->rules = $rules;

return $this;
}

/**
* Get rules
*
* @return string
*/
public function getRules()
{
return $this->rules;
}

/**
* Set kpiDetailsId
*
* @param string $kpiDetailsId
*
* @return KpiRules
*/
public function setKpiDetailsId($kpiDetailsId)
{
$this->kpiDetailsId = $kpiDetailsId;

return $this;
}

/**
* Get kpiDetailsId
*
* @return string
*/
public function getKpiDetailsId()
{
return $this->kpiDetailsId;
}
}