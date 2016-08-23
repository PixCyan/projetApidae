<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use JMS\Serializer\Annotation as JMS;

/**
 * Hebergement Cette classe regroupe et traite toutes les informations concernant les objets touristiques de type :
 * "HOTELLERIE", "HOTELLERIE_PLEIN_AIR", "HEBERGEMENT_LOCATIF", "HEBERGEMENT_COLLECTIF"
 *
 * @Entity
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\HebergementRepository")
 */
class Hebergement extends ObjetApidae
{

    /**
     * Le nombre de chambres classees
     * @var string
     *
     * @ORM\Column(name="nbChambresClassees", type="string", length=255, nullable=true)
     */
    private $nbChambresClassees;

    /**
     * La capacite d'hebergement
     * @var int
     *
     * @ORM\Column(name="capaciteHebergement", type="integer", nullable=true)
     */
    private $capaciteHebergement;

    /**
     * Le nombre de chambres declarees hotelier
     * @var int
     *
     * @ORM\Column(name="nbChambresDeclareesHotelier", type="integer", nullable=true)
     */
    private $nbChambresDeclareesHotelier;

    /**
     * Le nombre total de personnes possible
     * @var int
     *
     * @ORM\Column(name="nbTotalPersonnes", type="integer", nullable=true)
     */
    private $nbTotalPersonnes;

    /**
     * Le nombre de chambres simples
     * @var int
     *
     * @ORM\Column(name="nbChambresSimples", type="integer", nullable=true)
     */
    private $nbChambresSimples;

    /**
     * Le nombre de chambres doubles
     * @var int
     *
     * @ORM\Column(name="nbChambresDoubles", type="integer", nullable=true)
     */
    private $nbChambresDoubles;

    /**
     * Le nombre de suites
     * @var int
     *
     * @ORM\Column(name="nbSuites", type="integer", nullable=true)
     */
    private $nbSuites;

    /**
     * Le nombre de chambres à mobilier réduit
     * @var int
     *
     * @ORM\Column(name="nbChambresMobiliteReduite", type="integer", nullable=true)
     */
    private $nbChambresMobiliteReduite;

    /**
     * Naturisme autorise
     * @var bool
     *
     * @ORM\Column(name="naturisme", type="boolean", nullable=true)
     */
    private $naturisme;

    /**
     * La capacite totale de l'hebergement
     * @var int
     *
     * @ORM\Column(name="capaciteTotale", type="integer", nullable=true)
     */
    private $capaciteTotale;

    /**
     * La capacite total pour "jeunesse sport"
     * @var int
     *
     * @ORM\Column(name="capaciteTotaleJeunesseSport", type="integer", nullable=true)
     */
    private $capaciteTotaleJeunesseSport;

    /**
     * Le nombre d'hebergements pour une personne
     * @var int
     *
     * @ORM\Column(name="nbHebergementsUnePersonne", type="integer", nullable=true)
     */
    private $nbHebergementsUnePersonne;

    /**
     * Le nombre de lits doubles
     * @var int
     *
     * @ORM\Column(name="nbLitsDoubles", type="integer",  nullable=true)
     */
    private $nbLitsDoubles;

    /**
     * Le nombre de lits simples
     * @var int
     *
     * @ORM\Column(name="nbLitsSimples", type="integer", nullable=true)
     */
    private $nbLitsSimples;

    /**
     * Surface de l'hebergement
     * @var int
     *
     * @ORM\Column(name="surface", type="integer", nullable=true)
     */
    private $surface;

    /**
     * Numero d'etage si l'hebergement contient des etages
     * @var string
     *
     * @ORM\Column(name="numeroEtage", type="string", length=255, nullable=true)
     */
    private $numeroEtage;

    /**
     * Le nombre de pieces
     * @var int
     *
     * @ORM\Column(name="nbPieces", type="integer", nullable=true)
     */
    private $nbPieces;

    /**
     * La capacite max possible
     * @var int
     *
     * @ORM\Column(name="capaciteMaxPossible", type="integer", nullable=true)
     */
    private $capaciteMaxPossible;

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
     * Set nbChambresClassees
     *
     * @param string $nbChambresClassees
     *
     * @return Hebergement
     */
    public function setNbChambresClassees($nbChambresClassees)
    {
        $this->nbChambresClassees = $nbChambresClassees;

        return $this;
    }

    /**
     * Get nbChambresClassees
     *
     * @return string
     */
    public function getNbChambresClassees()
    {
        return $this->nbChambresClassees;
    }

    /**
     * Set nbChambresDeclareesHotelier
     *
     * @param string $nbChambresDeclareesHotelier
     *
     * @return Hebergement
     */
    public function setNbChambresDeclareesHotelier($nbChambresDeclareesHotelier)
    {
        $this->nbChambresDeclareesHotelier = $nbChambresDeclareesHotelier;

        return $this;
    }

    /**
     * Get nbChambresDeclareesHotelier
     *
     * @return string
     */
    public function getNbChambresDeclareesHotelier()
    {
        return $this->nbChambresDeclareesHotelier;
    }

    /**
     * Set nbTotalPersonnes
     *
     * @param string $nbTotalPersonnes
     *
     * @return Hebergement
     */
    public function setNbTotalPersonnes($nbTotalPersonnes)
    {
        $this->nbTotalPersonnes = $nbTotalPersonnes;

        return $this;
    }

    /**
     * Get nbTotalPersonnes
     *
     * @return string
     */
    public function getNbTotalPersonnes()
    {
        return $this->nbTotalPersonnes;
    }

    /**
     * Set nbChambresSimples
     *
     * @param string $nbChambresSimples
     *
     * @return Hebergement
     */
    public function setNbChambresSimples($nbChambresSimples)
    {
        $this->nbChambresSimples = $nbChambresSimples;

        return $this;
    }

    /**
     * Get nbChambresSimples
     *
     * @return string
     */
    public function getNbChambresSimples()
    {
        return $this->nbChambresSimples;
    }

    /**
     * Set nbChambresDoubles
     *
     * @param string $nbChambresDoubles
     *
     * @return Hebergement
     */
    public function setNbChambresDoubles($nbChambresDoubles)
    {
        $this->nbChambresDoubles = $nbChambresDoubles;

        return $this;
    }

    /**
     * Get nbChambresDoubles
     *
     * @return string
     */
    public function getNbChambresDoubles()
    {
        return $this->nbChambresDoubles;
    }

    /**
     * Set nbSuites
     *
     * @param string $nbSuites
     *
     * @return Hebergement
     */
    public function setNbSuites($nbSuites)
    {
        $this->nbSuites = $nbSuites;

        return $this;
    }

    /**
     * Get nbSuites
     *
     * @return string
     */
    public function getNbSuites()
    {
        return $this->nbSuites;
    }

    /**
     * Set nbChambresMobiliteReduite
     *
     * @param string $nbChambresMobiliteReduite
     *
     * @return Hebergement
     */
    public function setNbChambresMobiliteReduite($nbChambresMobiliteReduite)
    {
        $this->nbChambresMobiliteReduite = $nbChambresMobiliteReduite;

        return $this;
    }

    /**
     * Get nbChambresMobiliteReduite
     *
     * @return string
     */
    public function getNbChambresMobiliteReduite()
    {
        return $this->nbChambresMobiliteReduite;
    }

    /**
     * Set naturisme
     *
     * @param string $naturisme
     *
     * @return Hebergement
     */
    public function setNaturisme($naturisme)
    {
        $this->naturisme = $naturisme;

        return $this;
    }

    /**
     * Get naturisme
     *
     * @return string
     */
    public function getNaturisme()
    {
        return $this->naturisme;
    }

    /**
     * Set capaciteTotale
     *
     * @param string $capaciteTotale
     *
     * @return Hebergement
     */
    public function setCapaciteTotale($capaciteTotale)
    {
        $this->capaciteTotale = $capaciteTotale;

        return $this;
    }

    /**
     * Get capaciteTotale
     *
     * @return string
     */
    public function getCapaciteTotale()
    {
        return $this->capaciteTotale;
    }

    /**
     * Set capaciteTotaleJeunesseSport
     *
     * @param string $capaciteTotaleJeunesseSport
     *
     * @return Hebergement
     */
    public function setCapaciteTotaleJeunesseSport($capaciteTotaleJeunesseSport)
    {
        $this->capaciteTotaleJeunesseSport = $capaciteTotaleJeunesseSport;

        return $this;
    }

    /**
     * Get capaciteTotaleJeunesseSport
     *
     * @return string
     */
    public function getCapaciteTotaleJeunesseSport()
    {
        return $this->capaciteTotaleJeunesseSport;
    }

    /**
     * Set nbHebergementsUnePersonne
     *
     * @param string $nbHebergementsUnePersonne
     *
     * @return Hebergement
     */
    public function setNbHebergementsUnePersonne($nbHebergementsUnePersonne)
    {
        $this->nbHebergementsUnePersonne = $nbHebergementsUnePersonne;

        return $this;
    }

    /**
     * Get nbHebergementsUnePersonne
     *
     * @return string
     */
    public function getNbHebergementsUnePersonne()
    {
        return $this->nbHebergementsUnePersonne;
    }

    /**
     * Set nbLitsDoubles
     *
     * @param string $nbLitsDoubles
     *
     * @return Hebergement
     */
    public function setNbLitsDoubles($nbLitsDoubles)
    {
        $this->nbLitsDoubles = $nbLitsDoubles;

        return $this;
    }

    /**
     * Get nbLitsDoubles
     *
     * @return string
     */
    public function getNbLitsDoubles()
    {
        return $this->nbLitsDoubles;
    }

    /**
     * Set surface
     *
     * @param string $surface
     *
     * @return Hebergement
     */
    public function setSurface($surface)
    {
        $this->surface = $surface;

        return $this;
    }

    /**
     * Get surface
     *
     * @return string
     */
    public function getSurface()
    {
        return $this->surface;
    }

    /**
     * Set numeroEtage
     *
     * @param string $numeroEtage
     *
     * @return Hebergement
     */
    public function setNumeroEtage($numeroEtage)
    {
        $this->numeroEtage = $numeroEtage;

        return $this;
    }

    /**
     * Get numeroEtage
     *
     * @return string
     */
    public function getNumeroEtage()
    {
        return $this->numeroEtage;
    }

    /**
     * Set nbPieces
     *
     * @param string $nbPieces
     *
     * @return Hebergement
     */
    public function setNbPieces($nbPieces)
    {
        $this->nbPieces = $nbPieces;

        return $this;
    }

    /**
     * Get nbPieces
     *
     * @return string
     */
    public function getNbPieces()
    {
        return $this->nbPieces;
    }

    /**
     * @return int
     */
    public function getCapaciteHebergement()
    {
        return $this->capaciteHebergement;
    }

    /**
     * @param int $capaciteHebergement
     */
    public function setCapaciteHebergement($capaciteHebergement)
    {
        $this->capaciteHebergement = $capaciteHebergement;
    }

    /**
     * @return int
     */
    public function getNbLitsSimples()
    {
        return $this->nbLitsSimples;
    }

    /**
     * @param int $nbLitsSimples
     */
    public function setNbLitsSimples($nbLitsSimples)
    {
        $this->nbLitsSimples = $nbLitsSimples;
    }

    /**
     * @return int
     */
    public function getCapaciteMaxPossible()
    {
        return $this->capaciteMaxPossible;
    }

    /**
     * @param int $capaciteMaxPossible
     */
    public function setCapaciteMaxPossible($capaciteMaxPossible)
    {
        $this->capaciteMaxPossible = $capaciteMaxPossible;
    }

    /**
     * Traites les informations du tableau donne pour definir les informations de l'entite
     * @param $tab
     */
    public function setCapacite($tab) {
        //var_dump($tab);
        if(isset($tab->naturisme)) {
            $this->setNaturisme($tab->naturisme);
        } if(isset($tab->capaciteTotale)) {
            $this->setCapaciteTotale($tab->capaciteTotale);
        } if(isset($tab->capaciteTotaleJeunesseSport)) {
            $this->setCapaciteTotaleJeunesseSport($tab->capaciteTotaleJeunesseSport);
        } if(isset($tab->nombreHebergementsUnePersonne)) {
            $this->setNbHebergementsUnePersonne($tab->nombreHebergementsUnePersonne);
        } if(isset($tab->nombreSuites)) {
            $this->setNbSuites($tab->nombreSuites);
        } if(isset($tab->capaciteHebergement)) {
            $this->setCapaciteHebergement($tab->capaciteHebergement);
        } if(isset($tab->nombrePieces)) {
            $this->setNbPieces($tab->nombrePieces);
        } if(isset($tab->numeroEtages)) {
            $this->setNumeroEtage($tab->numeroEtages);
        } if(isset($tab->surface)) {
            $this->setSurface($tab->surface);
        } if(isset($tab->nombreLitsDoubles)) {
            $this->setNbLitsDoubles($tab->nombreLitsDoubles);
        } if(isset($tab->nombreChambresMobiliteReduite)) {
            $this->setNbChambresMobiliteReduite($tab->nombreChambresMobiliteReduite);
        } if(isset($tab->nombreChambresDoubles)) {
            $this->setNbChambresDoubles($tab->nombreChambresDoubles);
        } if(isset($tab->nombreChambresSimples)) {
            $this->setNbChambresSimples($tab->nombreChambresSimples);
        } if(isset($tab->nombreTotalPersonnes)) {
            $this->setNbChambresSimples($tab->nombreTotalPersonnes);
        } if(isset($tab->nombreChambresDeclareesHotelier)) {
            $this->setNbChambresDeclareesHotelier($tab->nombreChambresDeclareesHotelier);
        } if(isset($tab->nombreChambresClassees)) {
            $this->setNbChambresClassees($tab->nombreChambresClassees);
        }  if(isset($tab->nombreLitsSimples)) {
            $this->setNbLitsSimples($tab->nombreLitsSimples);
        } if(isset($tab->capaciteMaximumPossible)) {
            $this->setCapaciteMaxPossible($tab->capaciteMaximumPossible);
        }
    }
}

