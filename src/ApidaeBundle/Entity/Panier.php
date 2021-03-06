<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Panier Cette classe sert à traiter et regrouper toutes les informations concernant les objets touristiques rattachés à un panier
 * (ou "liste de favoris")
 *
 * @JMS\ExclusionPolicy("all")
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
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     *
     */
    private $id;

    /**
     * Libelle du panier
     * @var string
     *
     * @ORM\Column(name="panLibelle", type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $panLibelle;

    /**
     * Objets touristiques relies au panier
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="paniers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $objets;

    /**
     * Utilisateur proprietaire du panier
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserApidae", inversedBy="paniers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * ID du cookie si le panier a ete cree sans aucune session active
     * @var int
     *
     * @ORM\Column(name="idCookie", type="integer", nullable=true)
     */
    private $idCookie;


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
     * @return ArrayCollection
     */
    public function getObjets() {
        return $this->objets;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getIdCookie()
    {
        return $this->idCookie;
    }

    /**
     * @param mixed $idCookie
     */
    public function setIdCookie($idCookie)
    {
        $this->idCookie = $idCookie;
    }
}

