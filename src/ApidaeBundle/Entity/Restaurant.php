<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


class Restaurant
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
     * @ORM\Column(name="nbMaxCouverts", type="integer", length=255)
     */
    private $nbMaxCouverts;

    /**
     * @var int
     *
     * @ORM\Column(name="nbCouvertsTerrasse", type="integer")
     */
    private $nbCouvertsTerrasse;

    /**
     * @var int
     *
     * @ORM\Column(name="nbSalles", type="integer")
     */
    private $nbSalles;


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
}

