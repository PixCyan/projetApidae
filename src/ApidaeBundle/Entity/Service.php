<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Service
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
     * @var string
     *
     * @ORM\Column(name="serLibelle", type="string", length=255)
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
     * @ORM\Column(name="serType", type="string", length=255)
     */
    private $serType;

    /**
    * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", inversedBy="services")
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

