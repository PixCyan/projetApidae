<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InformationsTarif
 *
 * @ORM\Table(name="informations_tarif")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\InformationsTarifRepository")
 */
class InformationsTarif
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
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="tarifs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $objetApidae;

    /**
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\TarifType", inversedBy="infosTarif")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tarifType;

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
     * @return string
     */
    public function getTarIndication()
    {
        return $this->tarIndication;
    }

    /**
     * @param string $tarIndication
     */
    public function setTarIndication($tarIndication)
    {
        $this->tarIndication = $tarIndication;
    }

    /**
     * @return string
     */
    public function getTarDevise()
    {
        return $this->tarDevise;
    }

    /**
     * @param string $tarDevise
     */
    public function setTarDevise($tarDevise)
    {
        $this->tarDevise = $tarDevise;
    }

    /**
     * @return string
     */
    public function getTarMin()
    {
        return $this->tarMin;
    }

    /**
     * @param string $tarMin
     */
    public function setTarMin($tarMin)
    {
        $this->tarMin = $tarMin;
    }

    /**
     * @return string
     */
    public function getTarMax()
    {
        return $this->tarMax;
    }

    /**
     * @param string $tarMax
     */
    public function setTarMax($tarMax)
    {
        $this->tarMax = $tarMax;
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
}

