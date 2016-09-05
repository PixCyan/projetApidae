<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Index;

/**
 * SelectionApidae Cette classe regroupe et traite toutes les informations concernant les selections creees sur la plateforme Apidae.
 *
 * @ORM\Table(name="selection_apidae")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\SelectionApidaeRepository")
 */
class SelectionApidae
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
     * ID Apidae de la selection
     * @var int
     *
     * @ORM\Column(name="idSelectionApidae", type="integer", unique=true)
     */
    private $idSelectionApidae;

    /**
     * Libelle de la selection
     * @var string
     *
     * @ORM\Column(name="selLibelle", type="string", length=255)
     */
    private $selLibelle;

    /**
     * Objets touristiques auxquels la selection est rattachee
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="selectionsApidae", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $objets;

    /**
     * SelectionApidae constructor.
     */
    public function __construct() {
        $this->objets = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae
     * @param ObjetApidae $objet
     */
    public function addObjetApidae(ObjetApidae $objet) {
        $this->objets[] = $objet;

    }

    /**
     * Supprime objetApidae
     * @param ObjetApidae $objet
     */
    public function removeObjetApidae(ObjetApidae $objet) {
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
     * Set idSelectionApidae
     *
     * @param integer $idSelectionApidae
     *
     * @return SelectionApidae
     */
    public function setIdSelectionApidae($idSelectionApidae)
    {
        $this->idSelectionApidae = $idSelectionApidae;

        return $this;
    }

    /**
     * Get idSelectionApidae
     *
     * @return int
     */
    public function getIdSelectionApidae()
    {
        return $this->idSelectionApidae;
    }

    /**
     * Set selLibelle
     *
     * @param string $selLibelle
     *
     * @return SelectionApidae
     */
    public function setSelLibelle($selLibelle)
    {
        $this->selLibelle = $selLibelle;

        return $this;
    }

    /**
     * Get selLibelle
     *
     * @return string
     */
    public function getSelLibelle()
    {
        return $this->selLibelle;
    }

    /**
     *@return ArrayCollection
     */
    public function getObjets() {
        return $this->objets;
    }
}

