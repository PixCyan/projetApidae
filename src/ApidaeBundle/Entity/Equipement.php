<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Index;

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
    * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="equipements", cascade={"merge"})
    * @ORM\JoinColumn(nullable=false)
    */
    private $objetsApidae;

    public function __construct() {
        //initialisation des collections
        $this->objetsApidae = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     */
    public function addObjetApidae(ObjetApidae $tradObjet) {
        $this->objetsApidae[] = $tradObjet;
    }

    /**
     * Supprime objetApidae de la categorie
     */
    public function removeObjetApidae(ObjetApidae $tradObjet) {
        $this->objetsApidae->removeElement($tradObjet);
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
     * @return mixed
     */
    public function getObjetsApidae()
    {
        return $this->objetsApidae;
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

