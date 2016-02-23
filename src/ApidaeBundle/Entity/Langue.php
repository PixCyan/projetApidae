<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Langue
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
     * @var int
     *
     * @ORM\Column(name="codeLangue", type="integer", unique=true)
     */
    private $codeLangue;

    /**
     * @var string
     *
     * @ORM\Column(name="lan_Libelle", type="string", length=255)
     */
    private $lanLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="lan_ShortCut", type="string", length=255)
     */
    private $lanShortCut;

    /**
     * @var string
     *
     * @ORM\Column(name="lan_Iso", type="string", length=255)
     */
    private $lanIso;

    /**
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", mappedBy="langue")
     * @ORM\JoinColumn(name="idTrad", referencedColumnName="id", nullable=false)
     */
    private $traductions;



    public function _construct() {
        //initialisation des collections
        $this->traductions = new ArrayCollection();
    }

    /**
     * Ajoute/lie une traduction à l'objet
     */
    public function addTraduction(TraductionObjetApidae $traduction) {
        $this->traductions[] = $traduction;

    }

    /**
     * Supprime une traduction lié à l'objet
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
     *@return un tableau contenant les traductions associés à l'objetApidae
     */
    public function getTraductions() {
        return $this->traductions;
    }
}

