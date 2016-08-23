<?php

namespace ApidaeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Duree Cette classe regroupe et traite toutes les informations concernant les durees des "Activite".
 *
 * @ORM\Table(name="duree")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\DureeRepository")
 */
class Duree
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
     * ID Apidae de la duree
     * @var int
     *
     * @ORM\Column(name="idDuree", type="integer")
     */
    private $idDuree;

    /**
     * Libelle de la duree
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * Ordre d'importance
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer", length=255)
     */
    private $ordre;

    /**
     * Les activites auxquelles la duree est reliee
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Activite", inversedBy="durees")
     */
    protected $activites;

    /**
     * Activite constructor.
     */
    public function __construct()
    {
        $this->activites = new ArrayCollection();
    }

    /**
     * Ajoute/lie une traduction à l'entite
     */
    public function addActivite(Activite $a) {
        $this->activites[] = $a;
    }

    /**
     * Supprime une traduction lié à l'entite
     */
    public function removeActivite(Activite $a) {
        $this->activites->removeElement($a);
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Duree
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
     * @param string $ordre
     *
     * @return Duree
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return string
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
     * @return int
     */
    public function getIdDuree()
    {
        return $this->idDuree;
    }

    /**
     * @param int $idDuree
     */
    public function setIdDuree($idDuree)
    {
        $this->idDuree = $idDuree;
    }
}

