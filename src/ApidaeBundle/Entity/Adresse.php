<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adresse
 *
 * @ORM\Table(name="adresse")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\AdresseRepository")
 */
class Adresse
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
     * @var string
     *
     * @ORM\Column(name="codePostal", type="string", length=255)
     */
    private $codePostal;

    /**
     * @var int
     *
     * @ORM\Column(name="codeCommune", type="integer")
     */
    private $codeCommune;

    /**
     * @var string
     *
     * @ORM\Column(name="commune", type="string", length=255, nullable=true)
     */
    private $commune;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255)
     */
    private $adresse;

    /**
     *
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae",  mappedBy="adresse", cascade={"persist"})
     * @ORM\JoinColumn(name="idObjet", referencedColumnName="id", nullable=false)
     */
    private $objetsApidae;

    public function _construct() {
        //initialisation des collections
        $this->objets = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objet Apidae à l'adresse 
     */
    public function addObjetApidae(ObjetApidae $obj) {
        $this->objetsApidae[] = $obj;

    }

    /**
     * Supprime un objet Apidae lié à l'adresse
     */
    public function removeObjetApidae(ObjetApidae $obj) {
        $this->objetsApidae->removeElement($obj);
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
     * Set codePostal
     *
     * @param string $codePostal
     *
     * @return Adresse
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set codeCommune
     *
     * @param integer $codeCommune
     *
     * @return Adresse
     */
    public function setCodeCommune($codeCommune)
    {
        $this->codeCommune = $codeCommune;

        return $this;
    }

    /**
     * Get codeCommune
     *
     * @return int
     */
    public function getCodeCommune()
    {
        return $this->codeCommune;
    }

    /**
     * Set commune
     *
     * @param string $commune
     *
     * @return Adresse
     */
    public function setCommune($commune)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get commune
     *
     * @return string
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     *
     * @return Adresse
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }
}

