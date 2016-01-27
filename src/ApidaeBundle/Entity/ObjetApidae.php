<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjetApidae
 *
 * @ORM\Table(name="objet_apidae")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\ObjetApidaeRepository")
 */
class ObjetApidae
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
     * @ORM\Column(name="id_obj", type="integer")
     */
    private $id_obj;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_Etoile", type="string", length=255, nullable=true)
     */
    private $objEtoile;

    /**
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\Adresse", inversedBy="objetsApidae")
     * @ORM\JoinColumn(nullable = false)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_TypeApidae", type="string", length=255)
     */
    private $objTypeApidae;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_Geolocalisation", type="string", length=255, nullable=true)
     */
    private $objGeolocalisation;

    /**
     * @var bool
     *
     * @ORM\Column(name="obj_Suggestion", type="boolean", nullable=true)
     */
    private $objSuggestion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_DateSuggestion", type="datetime", nullable=true)
     */
    private $objDateSuggestion;

    /**
    * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", mappedBy="objet", cascade={"persist"})
    */
    private $traductions;

    /**
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\ObjetLie",  mappedBy="objet", cascade={"persist"})
     */
    private $objetsLies;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\LabelQualite", inversedBy="objets", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasLabelQualite")
     */
    private $labelsQualite;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Categorie", inversedBy="objets", cascade={"persist"})
     * @ORM\JoinTable(name="objetHascategories")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Panier", mappedBy="objets", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasPanier")
     */
    private $paniers;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\SelectionApidae", mappedBy="objets", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasSelection")
     */
    private $selectionsApidae;


    public function _construct() {
        //initialisation des collections
        $this->traductions = new ArrayCollection();
        $this->objetsLies = new ArrayCollection();
        $this->labelsQualite = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->paniers = new ArrayCollection();
        $this->selectionsApidae = new ArrayColleciton();
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

    /**
     * Ajoute/lie un objetApidae à l'objet 
     */
    public function addObjetLie(ObjetApidae $objetApidae) {
        $this->objetsLies[] = $objetApidae;

    }

    /**
     * Supprime un objetApidae à l'objet 
     */
    public function removeObjetLie(ObjetApidae $objetApidae) {
        $this->objetsLies->removeElement($objetApidae);
    }

    /**
     * Ajoute/lie un label de qualite à l'objet 
     */
    public function addLabelQualite(LabelQualite $labelQualite) {
        $this->labelsQualite[] = $labelQualite;

    }

    /**
     * Supprime un label de qualite à l'objet 
     */
    public function removeLabelQualite(LabelQualite $labelQualite) {
        $this->labelsQualite->removeElement($labelQualite);
    }

    /**
     * Ajoute/lie une categorie à l'objet 
     */
    public function addCategorie(Categorie $categorie) {
        $this->categories[] = $categorie;

    }

    /**
     * Supprime une categorie à l'objet 
     */
    public function removeCategorie(Categorie $categorie) {
        $this->categories->removeElement($categorie);
    }

    /**
     * Ajoute/lie un panier à l'objet 
     */
    public function addPanier(Panier $panier) {
        $this->paniers[] = $panier;

    }

    /**
     * Supprime un panier à l'objet 
     */
    public function removePanier(Panier $panier) {
        $this->paniers->removeElement($panier);
    }

    /**
     * Ajoute/lie une sélection d'objets (définie sur le site) à l'objet 
     */
    public function addSelectionApidae(SelectionApidae $selection) {
        $this->selectionsApidae[] = $selection;

    }

    /**
     * Supprime une sélection d'objets (définie sur le site) à l'objet 
     */
    public function removeSelectionApidae(SelectionApidae $selection) {
        $this->selectionsApidae->removeElement($selection);
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

    public function getIdObjet() {
        return $this->id_obj;
    }

    public function setIdObjet($id) {
        $this->id_obj = $id;
    }

    /**
     * Set objEtoile
     *
     * @param string $objEtoile
     *
     * @return ObjetApidae
     */
    public function setObjEtoile($objEtoile)
    {
        $this->objEtoile = $objEtoile;

        return $this;
    }

    /**
     * Get objEtoile
     *
     * @return string
     */
    public function getObjEtoile()
    {
        return $this->objEtoile;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     *
     * @return ObjetApidae
     */
    public function setObjAdresse(Adresse $objAdresse)
    {
        $this->adresse = $objAdresse;

        return $this;
    }

    /**
     * Get objAdresse
     *
     * @return string
     */
    public function getObjAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set objTypeApidae
     *
     * @param string $objTypeApidae
     *
     * @return ObjetApidae
     */
    public function setObjTypeApidae($objTypeApidae)
    {
        $this->objTypeApidae = $objTypeApidae;

        return $this;
    }

    /**
     * Get objTypeApidae
     *
     * @return string
     */
    public function getObjTypeApidae()
    {
        return $this->objTypeApidae;
    }

    /**
     * Set objGeolocalisation
     *
     * @param string $objGeolocalisation
     *
     * @return ObjetApidae
     */
    public function setObjGeolocalisation($objGeolocalisation)
    {
        $this->objGeolocalisation = $objGeolocalisation;

        return $this;
    }

    /**
     * Get objGeolocalisation
     *
     * @return string
     */
    public function getObjGeolocalisation()
    {
        return $this->objGeolocalisation;
    }

    /**
     * Set objSuggestion
     *
     * @param boolean $objSuggestion
     *
     * @return ObjetApidae
     */
    public function setObjSuggestion($objSuggestion)
    {
        $this->objSuggestion = $objSuggestion;

        return $this;
    }

    /**
     * Get objSuggestion
     *
     * @return bool
     */
    public function getObjSuggestion()
    {
        return $this->objSuggestion;
    }

    /**
     * Set objDateSuggestion
     *
     * @param \DateTime $objDateSuggestion
     *
     * @return ObjetApidae
     */
    public function setObjDateSuggestion($objDateSuggestion)
    {
        $this->objDateSuggestion = $objDateSuggestion;

        return $this;
    }

    /**
     * Get objDateSuggestion
     *
     * @return \DateTime
     */
    public function getObjDateSuggestion()
    {
        return $this->objDateSuggestion;
    }

    /**
     *@return un tableau contenant les traductions associés à l'objetApidae
     */
    public function getTraductions() {
        return $this->traductions;
    }

    /**
     *@return un tableau contenant les objetsLies associés à l'objetApidae
     */
    public function getObjetsLies() {
        return $this->objetsLies;
    }

    /**
     *@return un tableau contenant les labels associés à l'objetApidae
     */
    public function getLabelsQualite() {
        return $this->labelsQualite;
    }

    /**
     *@return un tableau contenant les categories associés à l'objetApidae
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     *@return un tableau contenant les paniers associés à l'objetApidae
     */
    public function getPaniers() {
        return $this->paniers;
    }

    /**
     *@return un tableau contenant les selectionApidae associés à l'objetApidae
     */
    public function getSelectionsApidae() {
        return $this->selectionsApidae;
    }

}

