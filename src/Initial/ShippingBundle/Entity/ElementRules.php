<?php
namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * ElementRules
 *
 * @ORM\Table(name="element_rules")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ElementRulesRepository")
 */
class ElementRules
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
    public $rules;

    /**
     * Set elementDetailsId
     *
     * @param string $elementDetailsId
     * @return SubElementDetails
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
     * @return ElementRules
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
}
?>