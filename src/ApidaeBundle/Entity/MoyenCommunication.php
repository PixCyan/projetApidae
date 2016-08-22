<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoyenCommunication Cette classe regroupe et traite toutes les informations concernant les différents moyens de communications
 * permettant un contact avec les prestataires des objets touristiques auxquels ils sont rattachés.
 *
 * @ORM\Table(name="moyen_communication")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\MoyenCommunicationRepository")
 */
class MoyenCommunication
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
     * ID Apidae du moyen de communication
     * @var int
     *
     * @ORM\Column(name="idMoyCom", type="integer")
     */
    private $idMoyCom;

    /**
     * Libelle du moyen de communication
     * @var string
     *
     * @ORM\Column(name="moyComLibelle", type="string", length=255, nullable=true)
     */
    private $moyComLibelle;

    /**
     * Coordonnees
     * @var string
     *
     * @ORM\Column(name="moyComCoordonnees", type="string", length=255)
     */
    private $moyComCoordonnees;

    /**
     * Les objets touristiques auxquels ce moyen de communication est rattache
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="moyensCommunications", cascade={"persist"})
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
     * Set moyComLibelle
     *
     * @param string $moyComLibelle
     *
     * @return MoyenCommunication
     */
    public function setMoyComLibelle($moyComLibelle)
    {
        $this->moyComLibelle = $moyComLibelle;

        return $this;
    }

    /**
     * Get moyComLibelle
     *
     * @return string
     */
    public function getMoyComLibelle()
    {
        return $this->moyComLibelle;
    }

    /**
     * Set moyComCoordonnees
     *
     * @param string $moyComCoordonnees
     *
     * @return MoyenCommunication
     */
    public function setMoyComCoordonnees($moyComCoordonnees)
    {
        $this->moyComCoordonnees = $moyComCoordonnees;

        return $this;
    }

    /**
     * Get moyComCoordonnees
     *
     * @return string
     */
    public function getMoyComCoordonnees()
    {
        return $this->moyComCoordonnees;
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
    public function getIdMoyCom()
    {
        return $this->idMoyCom;
    }

    /**
     * @param int $idMoyCom
     */
    public function setIdMoyCom($idMoyCom)
    {
        $this->idMoyCom = $idMoyCom;
    }
}


