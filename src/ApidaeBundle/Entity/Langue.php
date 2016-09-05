<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Index;
use JMS\Serializer\Annotation as JMS;

/**
 * Langue Cette classe regroupe et traite toutes les informations concernant les différentes langues utilisees par le site.
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Table(name="langue")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\LangueRepository")
 */
class Langue
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
     * Code de la langue
     * @var int
     *
     * @ORM\Column(name="codeLangue", type="integer", unique=true)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $codeLangue;

    /**
     * Libelle de la langue
     * @var string
     *
     * @ORM\Column(name="lan_Libelle", type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $lanLibelle;

    /**
     * Diminutif de la langue
     * @var string
     *
     * @ORM\Column(name="lan_ShortCut", type="string", length=255)
     */
    private $lanShortCut;

    /**
     * TODO
     * @var string
     *
     * @ORM\Column(name="lan_Iso", type="string", length=255)
     */
    private $lanIso;

    /**
     * Les traductions auxquels la langue est rattache
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", mappedBy="langue")
     * @ORM\JoinColumn(name="idTrad", referencedColumnName="id", nullable=false)
     */
    private $traductions;


    /**
     * Langue constructor.
     */
    public function __construct() {
        //initialisation des collections
        $this->traductions = new ArrayCollection();
    }

    /**
     * Ajoute/lie une traduction à l'entite
     * @param TraductionObjetApidae $traduction
     */
    public function addTraduction(TraductionObjetApidae $traduction) {
        $this->traductions[] = $traduction;

    }

    /**
     * Supprime une traduction lié à l'entite
     * @param TraductionObjetApidae $traduction
     */
    public function removeTraduction(TraductionObjetApidae $traduction) {
        $this->traductions->removeElement($traduction);
    }

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
     * Set codeLangue
     *
     * @param integer $codeLangue
     *
     * @return Langue
     */
    public function setCodeLangue($codeLangue)
    {
        $this->codeLangue = $codeLangue;

        return $this;
    }

    /**
     * Get codeLangue
     *
     * @return int
     */
    public function getCodeLangue()
    {
        return $this->codeLangue;
    }

    /**
     * Set lanLibelle
     *
     * @param string $lanLibelle
     *
     * @return Langue
     */
    public function setLanLibelle($lanLibelle)
    {
        $this->lanLibelle = $lanLibelle;

        return $this;
    }

    /**
     * Get lanLibelle
     *
     * @return string
     */
    public function getLanLibelle()
    {
        return $this->lanLibelle;
    }

    /**
     * Set lanShortCut
     *
     * @param string $lanShortCut
     *
     * @return Langue
     */
    public function setLanShortCut($lanShortCut)
    {
        $this->lanShortCut = $lanShortCut;

        return $this;
    }

    /**
     * Get lanShortCut
     *
     * @return string
     */
    public function getLanShortCut()
    {
        return $this->lanShortCut;
    }

    /**
     * Set lanIso
     *
     * @param string $lanIso
     *
     * @return Langue
     */
    public function setLanIso($lanIso)
    {
        $this->lanIso = $lanIso;

        return $this;
    }

    /**
     * Get lanIso
     *
     * @return string
     */
    public function getLanIso()
    {
        return $this->lanIso;
    }

    /**
     *@return ArrayCollection
     */
    public function getTraductions() {
        return $this->traductions;
    }
}

