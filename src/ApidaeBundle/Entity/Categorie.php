<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Categorie Cette classe regroupe et traite toutes les informations concernant les categories.
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Table(name="categorie")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\CategorieRepository")
 */
class Categorie
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
     * ID Apidae de la categorie
     *
     * @var int
     *
     * @ORM\Column(name="catId", type="integer", length=255, unique=true)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $catId;

    /**
     * Libelle de l'entite
     *
     * @var string
     *
     * @ORM\Column(name="catLibelle", type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $catLibelle;

    /**
     * Sous-type auquel appartient la categorie selon le schema de la plateforme Apidae
     *
     * @var string
     *
     * @ORM\Column(name="catRefType", type="string", length=255)
     */
    private $catRefType;

    /**
     * Les objets touristiques auxquels la categorie est rattachee
     *
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="categories", cascade={"persist"})
     */
    private $objets;


    public function __construct() {
        $this->objets = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     * @param ObjetApidae $objet
     */
    public function addObjet(ObjetApidae $objet) {
        $this->objets[] = $objet;
    }

    /**
     * Supprime objetApidae de la categorie
     * @param ObjetApidae $objet
     */
    public function removeObjet(ObjetApidae $objet) {
        $this->objets->removeElement($objet);
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
     * Set catLibelle
     *
     * @param string $catLibelle
     *
     * @return Categorie
     */
    public function setCatLibelle($catLibelle)
    {
        $this->catLibelle = $catLibelle;

        return $this;
    }

    /**
     * Get catLibelle
     *
     * @return string
     */
    public function getCatLibelle()
    {
        return $this->catLibelle;
    }

    /**
     *@return ArrayCollection
     */
    public function getObjets() {
        return $this->objets;
    }

    /**
     * @return int
     */
    public function getCatId()
    {
        return $this->catId;
    }

    /**
     * @param int $catId
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;
    }

    /**
     * @return mixed
     */
    public function getCatRefType()
    {
        return $this->catRefType;
    }

    /**
     * @param mixed $catRefType
     */
    public function setCatRefType($catRefType)
    {
        $this->catRefType = $catRefType;
    }
}
