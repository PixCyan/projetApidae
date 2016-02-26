<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/** @Entity */
class Restaurant extends ObjetApidae
{

    /**
     * @var int
     *
     * @ORM\Column(name="nbMaxCouverts", type="integer", length=255, nullable=true)
     */
    private $nbMaxCouverts;

    /**
     * @var int
     *
     * @ORM\Column(name="nbCouvertsTerrasse", type="integer", nullable=true)
     */
    private $nbCouvertsTerrasse;

    /**
     * @var int
     *
     * @ORM\Column(name="nbSalles", type="integer", nullable=true)
     */
    private $nbSalles;

    /**
     * Set nbMaxCouverts
     *
     * @param string $nbMaxCouverts
     *
     * @return Restaurant
     */
    public function setNbMaxCouverts($nbMaxCouverts)
    {
        $this->nbMaxCouverts = $nbMaxCouverts;

        return $this;
    }

    /**
     * Get nbMaxCouverts
     *
     * @return string
     */
    public function getNbMaxCouverts()
    {
        return $this->nbMaxCouverts;
    }

    /**
     * Set nbCouvertsTerrasse
     *
     * @param integer $nbCouvertsTerrasse
     *
     * @return Restaurant
     */
    public function setNbCouvertsTerrasse($nbCouvertsTerrasse)
    {
        $this->nbCouvertsTerrasse = $nbCouvertsTerrasse;

        return $this;
    }

    /**
     * Get nbCouvertsTerrasse
     *
     * @return int
     */
    public function getNbCouvertsTerrasse()
    {
        return $this->nbCouvertsTerrasse;
    }

    /**
     * Set nbSalles
     *
     * @param integer $nbSalles
     *
     * @return Restaurant
     */
    public function setNbSalles($nbSalles)
    {
        $this->nbSalles = $nbSalles;

        return $this;
    }

    /**
     * Get nbSalles
     *
     * @return int
     */
    public function getNbSalles()
    {
        return $this->nbSalles;
    }

    public function setCapacite($tab) {
        if(isset($tab->nombreMaximumCouverts)) {
            $this->setNbMaxCouverts($tab->nombreMaximumCouverts);
        }
        if(isset($tab->nombreCouvertsTerrasse)) {
            $this->setNbCouvertsTerrasse($tab->nombreCouvertsTerrasse);
        }
        if(isset($tab->nombreSalles)) {
            $this->setNbSalles($tab->nombreSalles);
        }
    }

}

