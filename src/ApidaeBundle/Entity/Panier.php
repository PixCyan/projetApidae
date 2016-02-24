<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Panier
 *
 * @ORM\Table(name="panier")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\PanierRepository")
 */
class Panier
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
     * @ORM\Column(name="panLibelle", type="string", length=255)
     */
    private $panLibelle;

    /**
     * @var int
     *
     * @ORM\Column(name="utiIdUtilisateur", type="integer")
     */
    private $utiIdUtilisateur;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="paniers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $objets;


    public function __construct() {
        $this->objets = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     */
    public function addTraduction(ObjetApidae $objet) {
        $this->objets[] = $objet;

    }

    /**
     * Supprime objetApidae de la categorie
     */
    public function removeTraduction(ObjetApidae $objet) {
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
     * Set panLibelle
     *
     * @param string $panLibelle
     *
     * @return Panier
     */
    public function setpanLibelle($panLibelle)
    {
        $this->panLibelle = $panLibelle;

        return $this;
    }

    /**
     * Get panLibelle
     *
     * @return string
     */
    public function getpanLibelle()
    {
        return $this->panLibelle;
    }

    /**
     * Set utiIdUtilisateur
     *
     * @param integer $utiIdUtilisateur
     *
     * @return SelectionApidaeHasObjetApidae
     */
    public function setutiIdUtilisateur($utiIdUtilisateur)
    {
        $this->utiIdUtilisateur = $utiIdUtilisateur;

        return $this;
    }

    /**
     * Get utiIdUtilisateur
     *
     * @return int
     */
    public function getutiIdUtilisateur()
    {
        return $this->utiIdUtilisateur;
    }

    /**
     *@return un tableau 
     */
    public function getObjets() {
        return $this->objets;
    }
}

