<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalculationRules
 *
 * @ORM\Table(name="calculation_rules")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\CalculationRulesRepository")
 */
class CalculationRules
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
     * @var string
     *
     * @ORM\Column(name="Rules", type="string", length=255)
     */
    private $rules;

    /**
     * @var string
     *
     * @ORM\Column(name="RuleConditions", type="string", length=255)
     */
    private $ruleConditions;

    /**
     * @var string
     *
     * @ORM\Column(name="RuleActions", type="string", length=255)
     */
    private $ruleActions;


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
     * Set kpiDetailsId
     *
     * @param string $kpiDetailsId
     * @return CalculationRules
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

    /**
     * Set elementDetailsId
     *
     * @param string $elementDetailsId
     * @return CalculationRules
     */
    public function setElementDetailsId($elementDetailsId)
    {
        $this->elementDetailsId = $elementDetailsId;

        return $this;
    }

    /**
     * Get elementDetailsId
     *
     * @return string 
     */
    public function getElementDetailsId()
    {
        return $this->elementDetailsId;
    }

    /**
     * Set rules
     *
     * @param string $rules
     * @return CalculationRules
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
     * Set ruleConditions
     *
     * @param string $ruleConditions
     * @return CalculationRules
     */
    public function setRuleConditions($ruleConditions)
    {
        $this->ruleConditions = $ruleConditions;

        return $this;
    }

    /**
     * Get ruleConditions
     *
     * @return string 
     */
    public function getRuleConditions()
    {
        return $this->ruleConditions;
    }

    /**
     * Set ruleActions
     *
     * @param string $ruleActions
     * @return CalculationRules
     */
    public function setRuleActions($ruleActions)
    {
        $this->ruleActions = $ruleActions;

        return $this;
    }

    /**
     * Get ruleActions
     *
     * @return string 
     */
    public function getRuleActions()
    {
        return $this->ruleActions;
    }
}
