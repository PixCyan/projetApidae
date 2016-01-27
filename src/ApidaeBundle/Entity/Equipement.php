<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Equipement
 *
 * @ORM\Table(name="equipement")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\EquipementRepository")
 */
class Equipement
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
     * @ORM\Column(name="equLibelle", type="string", length=255)
     */
    private $equLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="equInfosSup", type="string", length=255, nullable=true)
     */
    private $equInfosSup;

    /**
     * @var string
     *
     * @ORM\Column(name="equType", type="string", length=255)
     */
    private $equType;

    /**
    * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", inversedBy="equipements")
    * @ORM\JoinColumn(nullable=false)
    */
    private $traduction;

    //---------------------- Getter & Setter ----------------------//

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
     * Set equLibelle
     *
     * @param string $equLibelle
     *
     * @return Equipement
     */
    public function setEquLibelle($equLibelle)
    {
        $this->equLibelle = $equLibelle;

        return $this;
    }

    /**
     * Get equLibelle
     *
     * @return string
     */
    public function getEquLibelle()
    {
        return $this->equLibelle;
    }

    /**
     * Set equInfosSup
     *
     * @param string $equInfosSup
     *
     * @return Equipement
     */
    public function setEquInfosSup($equInfosSup)
    {
        $this->equInfosSup = $equInfosSup;

        return $this;
    }

    /**
     * Get equInfosSup
     *
     * @return string
     */
    public function getEquInfosSup()
    {
        return $this->equInfosSup;
    }

    /**
     * Set equType
     *
     * @param string $equType
     *
     * @return Equipement
     */
    public function setEquType($equType)
    {
        $this->equType = $equType;

        return $this;
    }

    /**
     * Get equType
     *
     * @return string
     */
    public function getEquType()
    {
        return $this->equType;
    }

    /**
     * Set traIdTraduction
     *
     * @param integer $traduction
     *
     * @return Equipement
     */
    public function setTraduction(TraductionObjetApidae $traduction)
    {
        $this->traduction = $traduction;

        return $this;
    }

    /**
     * Get traduction
     *
     * @return int
     */
    public function getTraduction()
    {
        return $this->traduction;
    }
}

