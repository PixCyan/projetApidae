<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TraductionObjetApidae
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
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tra_DescriptionCourte", type="string", length=255, nullable=true)
     */
    private $traDescriptionCourte;

    /**
     * @var string
     *
     * @ORM\Column(name="tra_DescriptionLongue", type="string", length=255, nullable=true)
     */
    private $traDescriptionLongue;

    /**
     * @var string
     *
     * @ORM\Column(name="tra_DescriptionPersonnalisee", type="string", length=255, nullable=true)
     */
    private $traDescriptionPersonnalisee;

    /**
     * @var string
     *
     * @ORM\Column(name="tra_BonsPlans", type="string", length=255, nullable=true)
     */
    private $traBonsPlans;

    /**
     * @var string
     *
     * @ORM\Column(name="tra_InfosSup", type="string", length=255, nullable=true)
     */
    private $traInfosSup;

    /**
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="traductions")
     * @ORM\JoinColumn(nullable = false)
     */
    private $objet;

    /**
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\Langue", inversedBy="traductions")
     * @ORM\JoinColumn(nullable = false)
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

}

