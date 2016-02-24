<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LabelQualite
 *
 * @ORM\Table(name="label_qualite")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\LabelQualiteRepository")
 */
class LabelQualite
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
     * @ORM\Column(name="labId", type="integer")
     */
    private $labId;

    /**
     * @var string
     *
     * @ORM\Column(name="labLibelle", type="string", length=255)
     */
    private $labLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="labClassement", type="string", length=255)
     */
    private $labClassement;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="labelsQualite")
     * @ORM\JoinColumn(nullable=true)
     */
    private $objetsApidae;


    public function __construct() {
        //initialisation des collections
        $this->objetsApidae = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae
     */
    public function addObjetApidae(ObjetApidae $objet) {
        $this->objetsApidae[] = $objet;
    }

    /**
     * Supprime un objetApidae
     */
    public function removeObjetApidae(ObjetApidae $objet) {
        $this->objetsApidae->removeElement($objet);
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
     * Set labLibelle
     *
     * @param string $labLibelle
     *
     * @return LabelQualite
     */
    public function setLabLibelle($labLibelle)
    {
        $this->labLibelle = $labLibelle;

        return $this;
    }

    /**
     * Get labLibelle
     *
     * @return string
     */
    public function getLabLibelle()
    {
        return $this->labLibelle;
    }

    /**
     * Set labClassement
     *
     * @param string $labClassement
     *
     * @return LabelQualite
     */
    public function setLabClassement($labClassement)
    {
        $this->labClassement = $labClassement;

        return $this;
    }

    /**
     * Get labClassement
     *
     * @return string
     */
    public function getLabClassement()
    {
        return $this->labClassement;
    }

    /**
     *@return un tableau 
     */
    public function getObjetsApidae() {
        return $this->objetsApidae;
    }

    /**
     * @return int
     */
    public function getLabId()
    {
        return $this->labId;
    }

    /**
     * @param int $labId
     */
    public function setLabId($labId)
    {
        $this->labId = $labId;
    }


}

