<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Index;
use JMS\Serializer\Annotation as JMS;

/**
 * Service
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Table(name="service")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\ServiceRepository")
 */
class Service
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
     * @ORM\Column(name="serId", type="integer", unique=true)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $serId;

    /**
     * @var string
     *
     * @ORM\Column(name="serLibelle", type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $serLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="serInfosSup", type="string", length=255, nullable=true)
     */
    private $serInfosSup;

    /**
     * @var string
     *
     * @ORM\Column(name="serType", type="string", length=255, nullable=true)
     */
    private $serType;

    /**
     * @var string
     *
     * @ORM\Column(name="serFamilleCritere", type="string", length=255, nullable=true)
     */
    private $serFamilleCritere;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="services", cascade={"merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $objetsApidae;

    public function __construct() {
        //initialisation des collections
        $this->objetsApidae = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     */
    public function addObjetApidae(ObjetApidae $tradObjet) {
        $this->objetsApidae[] = $tradObjet;
    }

    /**
     * Supprime objetApidae de la categorie
     */
    public function removeObjetApidae(ObjetApidae $tradObjet) {
        $this->objetsApidae->removeElement($tradObjet);
    }

    //---------------------- Getter & Setter ----------------------//
    /**
     * @return int
     */
    public function getSerId()
    {
        return $this->serId;
    }

    /**
     * @param int $serId
     */
    public function setSerId($serId)
    {
        $this->serId = $serId;
    }

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
     * Set serLibelle
     *
     * @param string $serLibelle
     *
     * @return Service
     */
    public function setSerLibelle($serLibelle)
    {
        $this->serLibelle = $serLibelle;

        return $this;
    }

    /**
     * Get serLibelle
     *
     * @return string
     */
    public function getSerLibelle()
    {
        return $this->serLibelle;
    }

    /**
     * Set serInfosSup
     *
     * @param string $serInfosSup
     *
     * @return Service
     */
    public function setSerInfosSup($serInfosSup)
    {
        $this->serInfosSup = $serInfosSup;

        return $this;
    }

    /**
     * Get serInfosSup
     *
     * @return string
     */
    public function getSerInfosSup()
    {
        return $this->serInfosSup;
    }

    /**
     * Set serType
     *
     * @param string $serType
     *
     * @return Service
     */
    public function setSerType($serType)
    {
        $this->serType = $serType;

        return $this;
    }

    /**
     * Get serType
     *
     * @return string
     */
    public function getSerType()
    {
        return $this->serType;
    }

    /**
     * Set serType
     *
     * @param string $serType
     *
     * @return Service
     */
    public function setSerFamilleCritere($serType)
    {
        $this->serFamilleCritere = $serType;

        return $this;
    }

    /**
     * Get serFamilleCritere
     *
     * @return string
     */
    public function getSerFamilleCritere()
    {
        return $this->serFamilleCritere;
    }

    /**
     * @return mixed
     */
    public function getObjets()
    {
        return $this->objetsApidae;
    }
}

