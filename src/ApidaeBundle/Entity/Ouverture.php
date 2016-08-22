<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ouverture Cette classe regroupe et traite toutes les informations concernant les heures/dates d'ouvreture pour chaque
 * objet touristique.
 *
 * @ORM\Table(name="ouverture")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\OuvertureRepository")
 */
class Ouverture
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
     * ID Apidae de l'objet ouverture
     * @var int
     *
     * @ORM\Column(name="idOuverture", type="integer", length=255)
     */
    private $idOuverture;

    /**
     * Date de debut d'ouverture
     * @var string
     *
     * @ORM\Column(name="ouvDateDebut", type="string", length=255)
     */
    private $ouvDateDebut;

    /**
     * Date de fin d'ouverture
     * @var string
     *
     * @ORM\Column(name="ouvDateFin", type="string", length=255)
     */
    private $ouvDateFin;

    /**
     * Informations supplémentaires
     * @var string
     *
     * @ORM\Column(name="ouvInfosSup", type="string", length=255, nullable=true)
     */
    private $ouvInfosSup;

    /**
     * Objet Apidae auquel est relie l'ouverture
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="ouvertures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $objetApidae;

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
     * Set ouvDateDebut
     *
     * @param string $ouvDateDebut
     *
     * @return Ouverture
     */
    public function setOuvDateDebut($ouvDateDebut)
    {
        $this->ouvDateDebut = $ouvDateDebut;

        return $this;
    }

    /**
     * Set serInfosSup
     *
     * @param string $ouvInfosSup
     *
     * @return Service
     */
    public function setOuvInfosSup($ouvInfosSup)
    {
        $this->ouvInfosSup = $ouvInfosSup;

        return $this;
    }

    /**
     * Get serInfosSup
     *
     * @return string
     */
    public function getOuvInfosSup()
    {
        return $this->ouvInfosSup;
    }

    /**
     * Get ouvDateDebut
     *
     * @return string
     */
    public function getOuvDateDebut()
    {
        return $this->ouvDateDebut;
    }

    /**
     * Set ouvDateFin
     *
     * @param string $ouvDateFin
     *
     * @return Ouverture
     */
    public function setOuvDateFin($ouvDateFin)
    {
        $this->ouvDateFin = $ouvDateFin;

        return $this;
    }

    /**
     * Get ouvDateFin
     *
     * @return string
     */
    public function getOuvDateFin()
    {
        return $this->ouvDateFin;
    }

    /**
     * @return mixed
     */
    public function getObjetApidae()
    {
        return $this->objetApidae;
    }

    /**
     * @param mixed $objetApidae
     */
    public function setObjetApidae($objetApidae)
    {
        $this->objetApidae = $objetApidae;
    }

    /**
     * @return int
     */
    public function getIdOuverture()
    {
        return $this->idOuverture;
    }

    /**
     * @param int $idOuverture
     */
    public function setIdOuverture($idOuverture)
    {
        $this->idOuverture = $idOuverture;
    }
}

