<?php

namespace ApidaeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActiviteType Cette classe regroupe et traite toutes les informations conernant les types d'activite.
 * Les types d'activite sont relies aux objets touristiques de type "activite" (voir classe Activite)
 *
 * @ORM\Table(name="activiteType")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\ActiviteTypeRepository")
 */
class ActiviteType
{
    /**
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * ID Apidae du type d'activite
     *
     * @var int
     *
     * @ORM\Column(name="idActiviteType", type="integer", unique=true)
     */
    private $idActiviteType;

    /**
     * Libelle du type d'activite
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * Ordre d'importance
     *
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * Sous-type auquel appartient le type d'activite selon le schema de la plateforme Apidae
     *
     * @var string
     *
     * @ORM\Column(name="refType", type="string", length=255)
     */
    private $refType;

    /**
     * Les activites auxquelles ce type est relie
     *
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\Activite", mappedBy="activiteType")
     */
    protected $activites;

    /**
     * ActiviteType constructor.
     */
    public function __construct()
    {
        //initialisation des collections
        $this->activites = new ArrayCollection();
    }

    /**
     * Ajoute/lie une traduction à l'objet
     */
    public function addActivite(Activite $activite) {
        $this->activites[] = $activite;
    }

    /**
     * Supprime une traduction lié à l'objet
     */
    public function removeActivite(Activite $activite) {
        $this->activites->removeElement($activite);
    }


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
     * Set idActivite
     *
     * @param integer $idActivite
     *
     * @return ActiviteType
     */
    public function setIdActivite($idActivite)
    {
        $this->idActiviteType = $idActivite;

        return $this;
    }

    /**
     * Get idActivite
     *
     * @return int
     */
    public function getIdActivite()
    {
        return $this->idActiviteType;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return ActiviteType
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return ActiviteType
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @return mixed
     */
    public function getActivites()
    {
        return $this->activites;
    }

    /**
     * @return mixed
     */
    public function getRefType()
    {
        return $this->refType;
    }

    /**
     * @param mixed $refType
     */
    public function setRefType($refType)
    {
        $this->refType = $refType;
    }
}

