<?php

namespace ApidaeBundle\Entity;

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

    public function setCapacite($tab)
    {
        // TODO: Implement setCapacite() method.
    }
}

