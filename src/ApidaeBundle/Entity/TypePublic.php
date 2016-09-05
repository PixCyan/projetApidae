<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Index;

/**
 * TypePublic Cette classe regroupe et traite toutes les informations concernant les différents types de public pouvant
 * être lies aux objets touristiques.
 *
 * @ORM\Table(name="type_public")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\TypePublicRepository")
 */
class TypePublic
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
     * ID Apidae de l'entite
     * @var int
     * @ORM\Column(name="typId", type="integer", unique=true)
     */
    private $typId;

    /**
     * Libelle de l'entite
     * @var string
     *
     * @ORM\Column(name="typLibelle", type="string", length=255, nullable=true)
     */
    private $typLibelle;

    /**
     * Sous-categorie Apidae
     * @var string
     *
     * @ORM\Column(name="familleCritere", type="string", length=255, nullable=true)
     */
    private $familleCritere;

    /**
     * Nombre minimum
     * @var string
     *
     * @ORM\Column(name="min", type="string", length=255, nullable=true)
     */
    private $min;

    /**
     * Nombre maximum
     * @var string
     *
     * @ORM\Column(name="max", type="string", length=255, nullable=true)
     */
    private $max;

    /**
     * ObjetApidae auxquels est rattachee l'entite
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="typesPublic", cascade={"merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $objetsApidae;

    public function __construct() {
        //initialisation des collections
        $this->objetsApidae = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     * @param ObjetApidae $tradObjet
     */
    public function addObjetApidae(ObjetApidae $tradObjet) {
        $this->objetsApidae[] = $tradObjet;
    }

    /**
     * Supprime objetApidae de la categorie
     * @param ObjetApidae $tradObjet
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
     * Set typLibelle
     *
     * @param string $typLibelle
     *
     * @return TypePublic
     */
    public function setTypLibelle($typLibelle)
    {
        $this->typLibelle = $typLibelle;

        return $this;
    }

    /**
     * Get typLibelle
     *
     * @return string
     */
    public function getTypLibelle()
    {
        return $this->typLibelle;
    }

    /**
     * Set familleCritere
     *
     * @param string $familleCritere
     *
     * @return TypePublic
     */
    public function setFamilleCritere($familleCritere)
    {
        $this->familleCritere = $familleCritere;

        return $this;
    }

    /**
     * Get familleCritere
     *
     * @return string
     */
    public function getFamilleCritere()
    {
        return $this->familleCritere;
    }

    /**
     * Set min
     *
     * @param string $min
     *
     * @return TypePublic
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get min
     *
     * @return string
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set max
     *
     * @param string $max
     *
     * @return TypePublic
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get max
     *
     * @return string
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return int
     */
    public function getTypId()
    {
        return $this->typId;
    }


    /**
     * @param int $typId
     */
    public function setTypId($typId)
    {
        $this->typId = $typId;
    }

    /**
     * @return mixed
     */
    public function getObjetsApidae()
    {
        return $this->objetsApidae;
    }

    /**
     * @param mixed $objetsApidae
     */
    public function setObjetsApidae($objetsApidae)
    {
        $this->objetsApidae = $objetsApidae;
    }




}

