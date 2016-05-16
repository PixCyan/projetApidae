<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoyenCommunication
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
     * @var int
     *
     * @ORM\Column(name="idMoyCom", type="integer")
     */
    private $idMoyCom;

    /**
     * @var string
     *
     * @ORM\Column(name="moyComLibelle", type="string", length=255, nullable=true)
     */
    private $moyComLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="moyComCoordonnees", type="string", length=255)
     */
    private $moyComCoordonnees;

    /**
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


