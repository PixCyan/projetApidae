<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Evenement Cette classe regroupe et traite toutes les informations concernant les objets touristiques de type "FETE_ET_MANIFESTATION".
 *
 *
 * @Entity
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\EvenementRepository")
 */
class Evenement extends ObjetApidae
{
    /**
     * La portee de l'evenement (regionale, etc)
     * @var string
     *
     * @ORM\Column(name="portee", type="string", nullable=true)
     */
    private $portee;

    /**
     * Ordre d'importance
     * @var int
     *
     * @ORM\Column(name="ordrePortee", type="integer", nullable=true)
     */
    private $ordrePortee;

    /**
     * La date de début de l'evenement
     * @var datetime
     *
     * @ORM\Column(name="dateDebut", type="datetime", length=255)
     */
    private $dateDebut;

    /**
     * La date de fin de l'evenement
     * @var datetime
     *
     * @ORM\Column(name="dateFin", type="datetime", length=255)
     */
    private $dateFin;

    /**
     * @return string
     */
    public function getPortee()
    {
        return $this->portee;
    }

    /**
     * @param string $portee
     */
    public function setPortee($portee)
    {
        $this->portee = $portee;
    }

    /**
     * @return int
     */
    public function getOrdrePortee()
    {
        return $this->ordrePortee;
    }

    /**
     * @param int $ordrePortee
     */
    public function setOrdrePortee($ordrePortee)
    {
        $this->ordrePortee = $ordrePortee;
    }

    /**
     * @return datetime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * @param int $ouvDateDebut
     */
    public function setDateDebut($ouvDateDebut)
    {
        $this->dateDebut = $ouvDateDebut;
    }

    /**
     * @return datetime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * @param int $ouvDateFin
     */
    public function setDateFin($ouvDateFin)
    {
        $this->dateFin = $ouvDateFin;
    }

    /**
     * Traites les informations du tableau donne pour definir les informations de l'objet
     * @param $tab
     */
    public function setCapacite($tab)
    {
        $this->setPortee($tab['libelle']);
        $this->setOrdrePortee($tab['ordre']);
        if(isset($tab['dateDebut'])) {
            $this->setDateDebut(date_create($tab['dateDebut']));
        }
        if(isset($tab['dateFin'])) {
            $this->setDateFin(date_create($tab['dateFin']));
        }
    }
}

