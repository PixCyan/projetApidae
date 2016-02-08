<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SelectionApidae
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
     * @var int
     *
     * @ORM\Column(name="idSelectionApidae", type="integer", unique=true)
     */
    private $idSelectionApidae;

    /**
     * @var string
     *
     * @ORM\Column(name="selLibelle", type="string", length=255, unique=true)
     */
    private $selLibelle;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="selectionsApidae")
     * @ORM\JoinColumn(nullable=true)
     */
    private $objets;


    public function _construct() {
        $this->objets = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objetApidae
     */
    public function addObjetApidae(ObjetApidae $objet) {
        $this->objets[] = $objet;

    }

    /**
     * Supprime objetApidae
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
     *@return un tableau 
     */
    public function getObjets() {
        return $this->objets;
    }
}

