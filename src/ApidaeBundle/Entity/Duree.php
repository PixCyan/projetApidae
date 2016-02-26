<?php

namespace ApidaeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Duree
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
     * @var int
     *
     * @ORM\Column(name="idDuree", type="integer")
     */
    private $idDuree;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer", length=255)
     */
    private $ordre;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Activite", inversedBy="durees")
     * @ORM\JoinTable(name="activiteHasDuree")
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
     * Ajoute/lie une traduction à l'objet
     */
    public function addDuree(Activite $a) {
        $this->activites[] = $a;
    }

    /**
     * Supprime une traduction lié à l'objet
     */
    public function removeDuree(Activite $a) {
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
}

