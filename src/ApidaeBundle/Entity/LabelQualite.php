<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LabelQualite
 *
 * @ORM\Table(name="label_qualite")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\LabelQualiteRepository")
 */
class LabelQualite
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
     * @ORM\Column(name="labLibelle", type="string", length=255)
     */
    private $labLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="labClassement", type="string", length=255)
     */
    private $labClassement;

    /**
     * @ORM\ManyToMany(targetEntity="ApidaeBundle\Entity\TraductionObjetApidae", mappedBy="labelsQualite")
     * @ORM\JoinColumn(nullable=true)
     */
    private $traductions;


    public function _construct() {
        $this->traductions = new ArrayCollection();
    }

    /**
     * Ajoute/lie une traduction au label
     */
    public function addTraduction(TraductionObjetApidae $trad) {
        $this->traductions[] = $trad;
    }

    /**
     * Supprime traduction du label
     */
    public function removeTraduction(TraductionObjetApidae $trad) {
        $this->traductions->removeElement($trad);
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
     * Set labLibelle
     *
     * @param string $labLibelle
     *
     * @return LabelQualite
     */
    public function setLabLibelle($labLibelle)
    {
        $this->labLibelle = $labLibelle;

        return $this;
    }

    /**
     * Get labLibelle
     *
     * @return string
     */
    public function getLabLibelle()
    {
        return $this->labLibelle;
    }

    /**
     * Set labClassement
     *
     * @param string $labClassement
     *
     * @return LabelQualite
     */
    public function setLabClassement($labClassement)
    {
        $this->labClassement = $labClassement;

        return $this;
    }

    /**
     * Get labClassement
     *
     * @return string
     */
    public function getLabClassement()
    {
        return $this->labClassement;
    }

    /**
     *@return un tableau 
     */
    public function getTraductions() {
        return $this->traductions;
    }
}

