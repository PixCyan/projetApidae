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
     * @ORM\Column(name="dureeSeance", type="string", length=255)
     */
    private $dureeSeance;

    /**
     * @var string
     *
     * @ORM\Column(name="nbJours", type="string", length=255)
     */
    private $nbJours;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Duree", inversedBy="activites")
     */
    protected $durees;

    /**
     * Activite constructor.
     * @param string $dureeSeance
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

    public function setCapacite($tab)
    {
        // TODO: Implement setCapacite() method.
    }
}

