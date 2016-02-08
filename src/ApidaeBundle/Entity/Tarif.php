<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tarif
 *
 * @ORM\Table(name="tarif")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\TarifRepository")
 */
class Tarif
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
     * @ORM\Column(name="tarLibelle", type="string", length=255)
     */
    private $tarLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="tarDevise", type="string", length=255)
     */
    private $tarDevise;

    /**
     * @var string
     *
     * @ORM\Column(name="tarIndication", type="string", length=255, nullable=true)
     */
    private $tarIndication;

    /**
     * @var string
     *
     * @ORM\Column(name="tarMin", type="string", length=255, nullable=true)
     */
    private $tarMin;

    /**
     * @var string
     *
     * @ORM\Column(name="tarMax", type="string", length=255, nullable=true)
     */
    private $tarMax;

    /**
    * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", inversedBy="tarifs")
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
     * Set tarLibelle
     *
     * @param string $tarLibelle
     *
     * @return Tarif
     */
    public function setTarLibelle($tarLibelle)
    {
        $this->tarLibelle = $tarLibelle;

        return $this;
    }

    /**
     * Get tarLibelle
     *
     * @return string
     */
    public function getTarLibelle()
    {
        return $this->tarLibelle;
    }

    /**
     * Set tarDevise
     *
     * @param string $tarDevise
     *
     * @return Tarif
     */
    public function setTarDevise($tarDevise)
    {
        $this->tarDevise = $tarDevise;

        return $this;
    }

    /**
     * Get tarDevise
     *
     * @return string
     */
    public function getTarDevise()
    {
        return $this->tarDevise;
    }


    /**
     * Set tarIndication
     *
     * @param string $tarIndication
     *
     * @return Tarif
     */
    public function setTarIndication($tarIndication)
    {
        $this->tarIndication = $tarIndication;

        return $this;
    }

    /**
     * Get tarIndication
     *
     * @return string
     */
    public function getTarIndication()
    {
        return $this->tarIndication;
    }


    /**
     * Set tarMin
     *
     * @param string $tarMin
     *
     * @return Tarif
     */
    public function setTarMin($tarMin)
    {
        $this->tarMin = $tarMin;

        return $this;
    }

    /**
     * Get tarMin
     *
     * @return string
     */
    public function getTarMin()
    {
        return $this->tarMin;
    }

    /**
     * Set tarMax
     *
     * @param string $tarMax
     *
     * @return Tarif
     */
    public function setTarMax($tarMax)
    {
        $this->tarMax = $tarMax;

        return $this;
    }

    /**
     * Get tarMax
     *
     * @return string
     */
    public function getTarMax()
    {
        return $this->tarMax;
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

