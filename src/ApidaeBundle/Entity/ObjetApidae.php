<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;


//SINGLE_TABLE or JOINED pour @InheritanceType
/**
 * ObjetApidae Cette classe regroupe toutes les informations communes pour chaque type d'objet touristique.
 * Elle a pour filles les classes : "Activite", "Evenement", "Hebergement", "Restaurant"
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"objetApidae" = "ObjetApidae", "restaurant" = "Restaurant", "hebergement" = "Hebergement",
 *      "activite" = "Activite", "evenement" = "Evenement", "sejourPackage" = "SejourPackage"})
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\ObjetApidaeRepository")
 */
abstract class ObjetApidae {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * ID Apidae de l'objet touristique
     * @var int
     *
     * @ORM\Column(name="id_obj", type="integer", unique=true)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $idObj;

    /**
     * Nombre d'etoile detenu par l'objet si celui ci est classe
     * @var string
     *
     * @ORM\Column(name="obj_Etoile", type="string", length=255, nullable=true)
     */
    protected $objEtoile;

    /**
     * Type Apidae de l'objet
     * @var string
     *
     * @ORM\Column(name="obj_TypeApidae", type="string", length=255)
     */
    protected $objTypeApidae;

    /**
     * Points de geolocalisation
     * @var string
     *
     * @ORM\Column(name="obj_Geolocalisation", type="string", length=255, nullable=true)
     */
    protected $objGeolocalisation;

    /**
     * Objet present mis en suggestion ou non
     * @var bool
     *
     * @ORM\Column(name="obj_Suggestion", type="boolean")
     */
    protected $objSuggestion;

    /**
     * Date de mise en suggestion si $objSuggestion est "true"
     * @var \DateTime
     *
     * @ORM\Column(name="obj_DateSuggestion", type="datetime", nullable=true)
     */
    protected $objDateSuggestion;

    /**
     * ID des Objets touristiques auxquels sont relies cet objet touristique
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\ObjetLie",  mappedBy="objet", cascade={"persist"})
     */
    protected $objetsLies;

    /**
     * Categories auxquelles sont rattachees l'objet touristique
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Categorie", inversedBy="objets", cascade={"persist"})
     * @ORM\JoinTable(name="objetHascategories")
     *
     * @JMS\Expose
     * @JMS\Type("ArrayCollection<ApidaeBundle\Entity\Categorie>")
     */
    protected $categories;

    /**
     * Les paniers auxquels sont rattaches l'objet
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Panier", mappedBy="objets", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasPanier")
     */
    protected $paniers;

    /**
     * Selection creees sous la plateforme Apidae auxquelles sont reliees l'objet
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\SelectionApidae", mappedBy="objets", cascade={"merge"})
     * @ORM\JoinTable(name="objetHasSelection")
     */
    protected $selectionsApidae;

    /**
     * Commune à laquelle est relie l'objet
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\Commune", inversedBy="objetsApidae", cascade={"persist"})
     *
     * @JMS\Expose
     * @JMS\Type("ApidaeBundle\Entity\Commune")
     */
    protected $commune;

    /**
     * Adresse de l'objet
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255)
     */
    protected $adresse;

    /**
     * Code postal de l'objet
     * @var string
     *
     * @ORM\Column(name="codePostal", type="string", length=255)
     */
    protected $codePostal;

    /**
     * Labels qualites auxquelles sont rattaches l'objet
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\LabelQualite", inversedBy="objetsApidae", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasLabelQualite")
     */
    protected $labelsQualite;

    //----------------------------------------- Changements trad -----------------------------------------------------//
    /**
     * Nom de l'objet
     * @var string
     *
     * @ORM\Column(name="obj_Nom", type="text", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $nom;

    /**
     * Date en clair, informations concernant les dates d'ouvertures
     * @var string
     *
     * @ORM\Column(name="obj_DateEnClair", type="text", nullable=true)
     */
    protected $dateEnClair;

    /**
     * Tarif en clair, informations concernant les tarifs
     * @var string
     *
     * @ORM\Column(name="obj_TarifEnClair", type="text",nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $tarifEnClair;

    /**
     * Les equipements auxquels l'objet est rattache
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Equipement", inversedBy="objetsApidae", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasEquipements")
     */
    protected $equipements;

    /**
     * Les services auxquels l'objet est rattache
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\Service", inversedBy="objetsApidae", cascade={"persist"})
     * @ORM\JoinTable(name="objetHasServices")
     *
     * @JMS\Expose
     * @JMS\Type("ArrayCollection<ApidaeBundle\Entity\Service>")
     */
    protected $services;

    /**
     * Les moyens de communications auxquels l'objet est rattache
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\MoyenCommunication", mappedBy="objetApidae", cascade={"persist"})
     */
    protected $moyensCommunications;

    /**
     * Les mutlimedias auxquels l'objet est rattache
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\Multimedia", mappedBy="objetApidae", cascade={"persist"})
     *
     * @JMS\Expose
     * @JMS\Type("ArrayCollection<ApidaeBundle\Entity\Multimedia>")
     */
    protected $multimedias;

    /**
     * Les tarifs auxquels l'objet est rattache
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\InformationsTarif", mappedBy="objetApidae", cascade={"persist"})
     */
    protected $tarifs;

    /**
     * Les ouvertures (details) auxquelles l'objet est rattache
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\Ouverture", mappedBy="objetApidae", cascade={"persist"})
     */
    protected $ouvertures;

    /**
     * Les types de public auxquels l'objet est rattache
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\TypePublic", mappedBy="objetsApidae", cascade={"persist"})
     */
    protected $typesPublic;

    /**
     * Les traductions auxquelles l'objet est rattache
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", mappedBy="objet", cascade={"persist"})
     *
     * @JMS\Expose
     * @JMS\Type("ArrayCollection<ApidaeBundle\Entity\TraductionObjetApidae>")
     */
    protected $traductions;

    /**
     * ObjetApidae constructor.
     */
    public function __construct() {
        //initialisation des collections
        $this->traductions = new ArrayCollection();
        $this->objetsLies = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->paniers = new ArrayCollection();
        $this->selectionsApidae = new ArrayCollection();
        $this->labelsQualite = new ArrayCollection();
        //Ajout
        $this->equipements = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->moyensCommunications = new ArrayCollection();
        $this->multimedias = new ArrayCollection();
        $this->tarifs = new ArrayCollection();
        $this->ouvertures = new ArrayCollection();
        $this->typesPublic = new ArrayCollection();
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
     * Ajoute/lie un objetApidae à l'objet
     */
    public function addObjetLie(ObjetLie $objetLie) {
        $this->objetsLies[] = $objetLie;
    }

    /**
     * Supprime un objetApidae à l'objet
     */
    public function removeObjetLie(ObjetLie $objetLie) {
        $this->objetsLies->removeElement($objetLie);
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

    //-- changements


    /**
     * Ajoute/lie un type de public à l'objet
     */
    public function addTypePublic(TypePublic $type) {
        $this->typesPublic[] = $type;

    }

    /**
     * Supprime un type de public lié à l'objet
     */
    public function removeTypePublic(TypePublic $type) {
        $this->typesPublic->removeElement($type);
    }

    /**
     * Ajoute/lie un equipement à l'objet
     */
    public function addEquipement(Equipement $equipement) {
        $this->equipements[] = $equipement;

    }

    /**
     * Supprime un equipement lié à l'objet
     */
    public function removeEquipement(Equipement $equipement) {
        $this->equipements->removeElement($equipement);
    }

    /**
     * Ajoute/lie un service à l'objet
     */
    public function addService(Service $service) {
        $this->services[] = $service;

    }

    /**
     * Supprime un service lié à l'objet
     */
    public function removeService(Service $service) {
        $this->services->removeElement($service);
    }

    /**
     * Ajoute/lie un  moyen de communication à l'objet
     */
    public function addMoyenCommunication(MoyenCommunication $moyenCommunication) {
        $this->moyensCommunications[] = $moyenCommunication;

    }

    /**
     * Supprime un moyen de communication lié à l'objet
     */
    public function removeMoyenCommunication(MoyenCommunication $moyenCommunication) {
        $this->moyensCommunications->removeElement($moyenCommunication);
    }

    /**
     * Ajoute/lie un media de communication à l'objet
     */
    public function addMultimedia(Multimedia $multimedia) {
        $this->multimedias[] = $multimedia;

    }

    /**
     * Supprime un media lié à l'objet
     */
    public function removeMultimedia(Multimedia $multimedia) {
        $this->multimedias->removeElement($multimedia);
    }

    /**
     * Ajoute/lie un tarif à l'objet
     */
    public function addInfoTarif(InformationsTarif $tarif) {
        $this->tarifs[] = $tarif;

    }

    /**
     * Supprime un tarif lié à l'objet
     */
    public function removeInfoTarif(InformationsTarif $tarif) {
        $this->tarifs->removeElement($tarif);
    }


    /**
     * Ajoute/lie une ouverture à l'objet
     */
    public function addOuverture(Ouverture $ouverture) {
        $this->ouvertures[] = $ouverture;

    }

    /**
     * Supprime une ouverture lié à l'objet
     */
    public function removeOuverture(Ouverture $ouverture) {
        $this->ouvertures->removeElement($ouverture);
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
        return $this->idObj;
    }

    public function setIdObjet($id) {
        $this->idObj = $id;
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
     *@return array contenant les traductions associés à l'objetApidae
     */
    public function getTraductions() {
        return $this->traductions;
    }

    /**
     *@return array tableau contenant les objetsLies associés à l'objetApidae
     */
    public function getObjetsLies() {
        return $this->objetsLies;
    }

    /**
     *@return array tableau contenant les labels associés à l'objetApidae
     */
    public function getLabelsQualite() {
        return $this->labelsQualite;
    }

    /**
     *@return array tableau contenant les categories associés à l'objetApidae
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     *@return array tableau contenant les paniers associés à l'objetApidae
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

    /**
     * @return mixed
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * @param mixed $commune
     */
    public function setCommune($commune)
    {
        $this->commune = $commune;
    }

    /**
     * @return mixed
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @param mixed $adresse
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    /**
     * @return string
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * @param string $codePostal
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;
    }


    //--- changements

    /**
     * @return string
     */
    public function getDateEnClair()
    {
        return $this->dateEnClair;
    }

    /**
     * @param string $dateEnClair
     */
    public function setDateEnClair($dateEnClair)
    {
        $this->dateEnClair = $dateEnClair;
    }

    /**
     * @return string
     */
    public function getTarifEnClair()
    {
        return $this->tarifEnClair;
    }

    /**
     * @param string $tarifEnClair
     */
    public function setTarifEnClair($tarifEnClair)
    {
        $this->tarifEnClair = $tarifEnClair;
    }

    /**
     * @return mixed
     */
    public function getEquipements()
    {
        return $this->equipements;
    }

    /**
     * @param mixed $equipements
     */
    public function setEquipements($equipements)
    {
        $this->equipements = $equipements;
    }

    /**
     * @return mixed
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param mixed $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @return mixed
     */
    public function getMoyensCommunications()
    {
        return $this->moyensCommunications;
    }

    /**
     * @param mixed $moyensCommunications
     */
    public function setMoyensCommunications($moyensCommunications)
    {
        $this->moyensCommunications = $moyensCommunications;
    }

    /**
     * @return mixed
     */
    public function getMultimedias()
    {
        return $this->multimedias;
    }

    /**
     * @param mixed $multimedias
     */
    public function setMultimedias($multimedias)
    {
        $this->multimedias = $multimedias;
    }

    /**
     * @return mixed
     */
    public function getInfosTarif()
    {
        return $this->tarifs;
    }

    /**
     * @return mixed
     */
    public function getOuvertures()
    {
        return $this->ouvertures;
    }

    /**
     * @param mixed $ouvertures
     */
    public function setOuvertures($ouvertures)
    {
        $this->ouvertures = $ouvertures;
    }

    /**
     * @return mixed
     */
    public function getTypesPublic()
    {
        return $this->typesPublic;
    }

    /**
     * @param mixed $typesPublic
     */
    public function setTypesPublic($typesPublic)
    {
        $this->typesPublic = $typesPublic;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getTarifs()
    {
        return $this->tarifs;
    }

    /**
     * @param mixed $tarifs
     */
    public function setTarifs($tarifs)
    {
        $this->tarifs = $tarifs;
    }

    /**
     * Traites les informations du tableau donne pour definir les informations de l'objet
     * @param $tab
     */
    public abstract function setCapacite($tab);

    protected function getTradLangue($langue) {
        foreach($this->getTraductions() as $value) {
            if($value->getLangue() == $langue) {
                return $value;
            }
        }

        return null;
    }

}
