<?php

namespace Initial\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScorecardFolder
 *
 * @ORM\Table(name="scorecard_folder")
 * @ORM\Entity(repositoryClass="Initial\ShippingBundle\Repository\ScorecardFolderRepository")
 */
class ScorecardFolder
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
     * @ORM\Column(name="folderName", type="string", length=255)
     */
    private $folderName;


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
     * Set folderName
     *
     * @param string $folderName
     *
     * @return RankingFolder
     */
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;

        return $this;
    }

    /**
     * Get folderName
     *
     * @return string
     */
    public function getFolderName()
    {
        return $this->folderName;
    }
}

