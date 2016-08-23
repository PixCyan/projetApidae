<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Index;
use JMS\Serializer\Annotation as JMS;

/**
 * LabelQualite Cette classe regroupe et traite toutes le sinformations concernant les "Labels de Qualité" rattachés à certains
 * objets touristiques.
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Table(name="label_qualite", indexes={@Index(name="search_labId", columns={"labId"})})
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
     * ID Apidae du label
     * @var int
     *
     * @ORM\Column(name="labId", type="integer")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $labId;

    /**
     * Libelle du label
     * @var string
     *
     * @ORM\Column(name="labLibelle", type="string", length=255)
     *
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $labLibelle;

    /**
     * Classement du label
     * @var string
     *
     * @ORM\Column(name="labClassement", type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $labClassement;

    /**
     * Les objets touristiques auxquels ce label est rattache
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="labelsQualite")
     * @ORM\JoinColumn(nullable=true)
     */
    private $objetsApidae;

    /**
     * LabelQualite constructor.
     */
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
     *@return ArrayCollection
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

