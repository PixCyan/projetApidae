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
     * @var string
     *
     * @ORM\Column(name="moyComLibelle", type="string", length=255)
     */
    private $moyComLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="moyComCoordonnees", type="string", length=255)
     */
    private $moyComCoordonnees;

    /**
    * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", inversedBy="moyensCommunications")
    * @ORM\JoinColumn(nullable=false)
    */
    private $traduction;


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
     * Set traIdTraduction
     *
     * @param integer $traduction
     *
     * @return Equipement
     */
    public function setTraduction(TraductionObjetApidae $traduction)
    {
        $this->traduction = $traduction;

        return $this;
    }

    /**
     * Get traduction
     *
     * @return int
     */
    public function getTraduction()
    {
        return $this->traduction;
    }
}
