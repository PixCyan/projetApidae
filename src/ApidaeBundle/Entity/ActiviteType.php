<?php

namespace ApidaeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActiviteType
 *
 * @ORM\Table(name="activiteType")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\ActiviteTypeRepository")
 */
class ActiviteType
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
     *
     * @ORM\Column(name="idActiviteType", type="integer", unique=true)
     */
    private $idActiviteType;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\Activite", mappedBy="activiteType")
     */
    protected $activites;


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
}

