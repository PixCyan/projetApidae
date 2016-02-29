<?php

namespace ApidaeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/** @Entity */
class Activite extends ObjetApidae
{
    /**
     * @var string
     *
     * @ORM\Column(name="dureeSeance", type="string", length=255, nullable=true)
     */
    private $dureeSeance;

    /**
     * @var string
     *
     * @ORM\Column(name="nbJours", type="string", length=255, nullable=true)
     */
    private $nbJours;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Duree", mappedBy="activites")
     */
    protected $durees;

    /**
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ActiviteType",  inversedBy="activites")
     */
    protected $activiteType;

    /**
     * Activite constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->durees = new ArrayCollection();
    }

    /**
     * Ajoute/lie une duree
     */
    public function addDuree(Duree $d) {
        $this->durees[] = $d;
    }

    /**
     * Supprime une duree
     */
    public function removeDuree(Duree $d) {
        $this->durees->removeElement($d);
    }


    /**
     * Set duree
     *
     * @param string $duree
     *
     * @return Activite
     */
    public function setDureeSeance($duree)
    {
        $this->dureeSeance = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return string
     */
    public function getDureeSeance()
    {
        return $this->dureeSeance;
    }

    /**
     * @return mixed
     */
    public function getDurees()
    {
        return $this->durees;
    }

    /**
     * @return string
     */
    public function getNbJours()
    {
        return $this->nbJours;
    }

    /**
     * @param string $nbJours
     */
    public function setNbJours($nbJours)
    {
        $this->nbJours = $nbJours;
    }

    /**
     * @return mixed
     */
    public function getActiviteType()
    {
        return $this->activiteType;
    }

    /**
     * @param mixed $activiteType
     */
    public function setActiviteType($activiteType)
    {
        $this->activiteType = $activiteType;
    }

    public function setCapacite($tab)
    {
        if(isset($tab['dureeSeance']) && $tab['dureeSeance'] != null) {
            $this->setDureeSeance($tab['dureeSeance']);
        }
        if(isset($tab['nbJours']) && $tab['nbJours'] != null) {
            $this->setNbJours($tab['nbJours']);
        }
    }

}

