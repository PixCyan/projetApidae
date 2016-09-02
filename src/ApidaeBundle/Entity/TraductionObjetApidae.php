<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use JMS\Serializer\Annotation as JMS;

/**
 * TraductionObjetApidae Cette classe regroupe et traite toutes les informations concernant les traductions pour chaque objet touristique et pour
 * chaque langue disponible sur le site.
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Table(name="traduction_objet_apidae")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\TraductionObjetApidaeRepository")
 */
class TraductionObjetApidae
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
     */
    private $id;

    /**
     * Description courte
     * @var string
     *
     * @ORM\Column(name="tra_DescriptionCourte", type="text", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $traDescriptionCourte;

    /**
     * Description longue
     * @var string
     *
     * @ORM\Column(name="tra_DescriptionLongue", type="text", nullable=true)
     */
    private $traDescriptionLongue;

    /**
     * Description personnalisee (enrichissement de l'objet touristique par un admin)
     * @var string
     *
     * @ORM\Column(name="tra_DescriptionPersonnalisee", type="text", nullable=true)
     */
    private $traDescriptionPersonnalisee;

    /**
     * Bons plans (ex : "Tous les vendredi formule petit déj à 2 euros") (enrichissement de l'objet touristique par un admin)
     * @var string
     *
     * @ORM\Column(name="tra_BonsPlans", type="text", nullable=true)
     */
    private $traBonsPlans;

    /**
     * Informations supplémentaires (enrichissement de l'objet touristique par un admin)
     * @var string
     *
     * @ORM\Column(name="tra_InfosSup", type="text", nullable=true)
     */
    private $traInfosSup;

    /**
     * Désigne si l'on affiche la description personnalise ou non
     * @var bool
     * @ORM\Column(name="objShowDescrPerso", type="boolean")
     */
    private $objShowDescrPerso;

    /**
     * Désigne si l'on affiche les bons plans ou non
     * @var bool
     * @ORM\Column(name="objShowBonsPlans", type="boolean")
     */
    private $objShowBonsPlans;

    /**
     * Désigne si l'on affiche les informations supplémentaires
     * @var bool
     * @ORM\Column(name="objShowInfoSUp", type="boolean")
     */
    private $objShowInfoSUp;

    /**
     * ObjetApidae propriétaire de cette entite
     *
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="traductions")
     * @ORM\JoinColumn(nullable = false)
     */
    private $objet;

    /**
     * Langue rattachee a cette entite
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\Langue", inversedBy="traductions")
     * @ORM\JoinColumn(nullable = false)
     *
     * @JMS\Expose
     * @JMS\Type("ApidaeBundle\Entity\Langue")
     */
    private $langue;

    //---------------------- Getter & Setter ----------------------//

    /**
     * @return string
     */
    public function getTraDescriptionCourte()
    {
        return $this->traDescriptionCourte;
    }

    /**
     * @param string $traDescriptionCourte
     */
    public function setTraDescriptionCourte($traDescriptionCourte)
    {
        $this->traDescriptionCourte = $traDescriptionCourte;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTraDescriptionLongue()
    {
        return $this->traDescriptionLongue;
    }

    /**
     * @param string $traDescriptionLongue
     */
    public function setTraDescriptionLongue($traDescriptionLongue)
    {
        $this->traDescriptionLongue = $traDescriptionLongue;
    }

    /**
     * @return string
     */
    public function getTraDescriptionPersonnalisee()
    {
        return $this->traDescriptionPersonnalisee;
    }

    /**
     * @param string $traDescriptionPersonnalisee
     */
    public function setTraDescriptionPersonnalisee($traDescriptionPersonnalisee)
    {
        $this->traDescriptionPersonnalisee = $traDescriptionPersonnalisee;
    }

    /**
     * @return string
     */
    public function getTraBonsPlans()
    {
        return $this->traBonsPlans;
    }

    /**
     * @param string $traBonsPlans
     */
    public function setTraBonsPlans($traBonsPlans)
    {
        $this->traBonsPlans = $traBonsPlans;
    }

    /**
     * @return string
     */
    public function getTraInfosSup()
    {
        return $this->traInfosSup;
    }

    /**
     * @param string $traInfosSup
     */
    public function setTraInfosSup($traInfosSup)
    {
        $this->traInfosSup = $traInfosSup;
    }

    /**
     * @return mixed
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * @param mixed $objet
     */
    public function setObjet(ObjetApidae $objet)
    {
        $this->objet = $objet;
    }

    /**
     * @return mixed
     */
    public function getLangue()
    {
        return $this->langue;
    }

    /**
     * @param mixed $langue
     */
    public function setLangue($langue)
    {
        $this->langue = $langue;
    }

    /**
     * @return boolean
     */
    public function isObjShowDescrPerso()
    {
        return $this->objShowDescrPerso;
    }

    /**
     * @param boolean $objShowDescrPerso
     */
    public function setObjShowDescrPerso($objShowDescrPerso)
    {
        $this->objShowDescrPerso = $objShowDescrPerso;
    }

    /**
     * @return boolean
     */
    public function isObjShowBonsPlans()
    {
        return $this->objShowBonsPlans;
    }

    /**
     * @param boolean $objShowBonsPlans
     */
    public function setObjShowBonsPlans($objShowBonsPlans)
    {
        $this->objShowBonsPlans = $objShowBonsPlans;
    }

    /**
     * @return boolean
     */
    public function isObjShowInfoSUp()
    {
        return $this->objShowInfoSUp;
    }

    /**
     * @param boolean $objShowInfoSUp
     */
    public function setObjShowInfoSUp($objShowInfoSUp)
    {
        $this->objShowInfoSUp = $objShowInfoSUp;
    }
}

