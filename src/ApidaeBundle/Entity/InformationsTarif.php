<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InformationsTarif Cette classe regroupe et traite toutes les informations concernant les differents tarifs
 * associés aux "TarifType" (ex :"menu enfant") disponibles pour chaque objets touristiques.
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
     * La devise utilise
     * @var string
     *
     * @ORM\Column(name="tarDevise", type="string", length=255)
     */
    private $tarDevise;

    /**
     * Les informations importantes/supplementaires
     * @var string
     *
     * @ORM\Column(name="tarIndication", type="string", length=255, nullable=true)
     */
    private $tarIndication;

    /**
     * Le tarif minimal
     * @var string
     *
     * @ORM\Column(name="tarMin", type="string", length=255, nullable=true)
     */
    private $tarMin;

    /**
     * Le tarif maximal
     * @var string
     *
     * @ORM\Column(name="tarMax", type="string", length=255, nullable=true)
     */
    private $tarMax;

    /**
     * Les objets touristiques auxquels ce tarif est rattache
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="tarifs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $objetApidae;

    /**
     * Le type de tarif auquel ce tarif  est rattache
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

    /**
     * @return mixed
     */
    public function getTarifType()
    {
        return $this->tarifType;
    }

    /**
     * @param mixed $tarifType
     */
    public function setTarifType($tarifType)
    {
        $this->tarifType = $tarifType;
    }
}

