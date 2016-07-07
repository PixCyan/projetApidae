<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Multimedia
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Table(name="multimedia")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\MultimediaRepository")
 */
class Multimedia
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
     * @ORM\Column(name="idMultimedia", type="integer", nullable=true)
     *
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $idMultimedia;

    /**
     * @var string
     *
     * @ORM\Column(name="mulLibelle", type="string", length=255, nullable=true)
     */
    private $mulLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="mulType", type="string", length=255)
     */
    private $mulType;

    /**
     * @var string
     *
     * @ORM\Column(name="mulUrl", type="string", length=255, nullable=true)
     *
     */
    private $mulUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="mulUrlListe", type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $mulUrlListe;

    /**
     * @var string
     *
     * @ORM\Column(name="mulUrlFiche", type="string", length=255, nullable=true)
     */
    private $mulUrlFiche;

    /**
     * @var string
     *
     * @ORM\Column(name="mulUrlDiapo", type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $mulUrlDiapo;

    /**
     * @var bool
     *
     * @ORM\Column(name="mulLocked", type="boolean")
     */
    private $mulLocked;

    /**
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="multimedias")
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
     * Set mulLibelle
     *
     * @param string $mulLibelle
     *
     * @return Multimedia
     */
    public function setMulLibelle($mulLibelle)
    {
        $this->mulLibelle = $mulLibelle;

        return $this;
    }

    /**
     * Get mulLibelle
     *
     * @return string
     */
    public function getMulLibelle()
    {
        return $this->mulLibelle;
    }

    /**
     * Set mulType
     *
     * @param string $mulType
     *
     * @return Multimedia
     */
    public function setMulType($mulType)
    {
        $this->mulType = $mulType;

        return $this;
    }

    /**
     * Get mulType
     *
     * @return string
     */
    public function getMulType()
    {
        return $this->mulType;
    }

    /**
     * Set mulUrlListe
     *
     * @param string $mulUrlListe
     *
     * @return Multimedia
     */
    public function setMulUrlListe($mulUrlListe)
    {
        $this->mulUrlListe = $mulUrlListe;

        return $this;
    }

    /**
     * Get mulUrlListe
     *
     * @return string
     */
    public function getMulUrlListe()
    {
        return $this->mulUrlListe;
    }

    /**
     * Set mulUrlFiche
     *
     * @param string $mulUrlFiche
     *
     * @return Multimedia
     */
    public function setMulUrlFiche($mulUrlFiche)
    {
        $this->mulUrlFiche = $mulUrlFiche;

        return $this;
    }

    /**
     * Get mulUrlFiche
     *
     * @return string
     */
    public function getMulUrlFiche()
    {
        return $this->mulUrlFiche;
    }

    /**
     * Set mulUrlDiapo
     *
     * @param string $mulUrlDiapo
     *
     * @return Multimedia
     */
    public function setMulUrlDiapo($mulUrlDiapo)
    {
        $this->mulUrlDiapo = $mulUrlDiapo;

        return $this;
    }

    /**
     * Get mulUrlDiapo
     *
     * @return string
     */
    public function getMulUrlDiapo()
    {
        return $this->mulUrlDiapo;
    }

    /**
     * Set mulLocked
     *
     * @param boolean $mulLocked
     *
     * @return Multimedia
     */
    public function setMulLocked($mulLocked)
    {
        $this->mulLocked = $mulLocked;

        return $this;
    }

    /**
     * Get mulLocked
     *
     * @return bool
     */
    public function getMulLocked()
    {
        return $this->mulLocked;
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
    public function getIdMultimedia()
    {
        return $this->idMultimedia;
    }

    /**
     * @param int $idMultimedia
     */
    public function setIdMultimedia($idMultimedia)
    {
        $this->idMultimedia = $idMultimedia;
    }

    /**
     * @return string
     */
    public function getMulUrl()
    {
        return $this->mulUrl;
    }

    /**
     * @param string $mulUrl
     */
    public function setMulUrl($mulUrl)
    {
        $this->mulUrl = $mulUrl;
    }
}

