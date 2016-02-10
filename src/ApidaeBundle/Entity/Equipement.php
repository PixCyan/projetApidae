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
     * @var int
     * @ORM\Column(name="equId", type="integer", unique=true)
     */
    private $equId;

    /**
     * @var string
     *
     * @ORM\Column(name="equLibelle", type="string", length=255, nullable=true)
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
    * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", mappedBy="equipements", cascade={"merge"})
    * @ORM\JoinColumn(nullable=false)
    */
    private $traductions;

    public function _construct() {
        //initialisation des collections
        $this->traductions = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     */
    public function addTraduction(TraductionObjetApidae $tradObjet) {
        $this->traductions[] = $tradObjet;
    }

    /**
     * Supprime objetApidae de la categorie
     */
    public function removeObjet(TraductionObjetApidae $tradObjet) {
        $this->traductions->removeElement($tradObjet);
    }

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

    /**
     * @return int
     */
    public function getEquId()
    {
        return $this->equId;
    }

    /**
     * @param int $equId
     */
    public function setEquId($equId)
    {
        $this->equId = $equId;
    }


}

