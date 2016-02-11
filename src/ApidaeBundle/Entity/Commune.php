<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commune
 *
 * @ORM\Table(name="commune")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\CommuneRepository")
 */
class Commune
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
     * @ORM\Column(name="comId", type="integer", unique=true)
     */
    private $comId;

    /**
     * @var string
     *
     * @ORM\Column(name="comCode", type="string", length=255)
     */
    private $comCode;

    /**
     * @var string
     *
     * @ORM\Column(name="comNom", type="string", length=255)
     */
    private $comNom;


    /**
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="commune")
     */
    private $objetsApidae;

    public function _construct() {
        //initialisation des collections
        $this->objetsApidae = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objet Apidae à l'adresse
     */
    public function addObjetApidae(ObjetApidae $obj) {
        $this->objetsApidae[] = $obj;
    }

    /**
     * Supprime un objet Apidae lié à l'adresse
     */
    public function removeObjetApidae(ObjetApidae $obj) {
        $this->objetsApidae->removeElement($obj);
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
     * Set idCommune
     *
     * @param integer $idCommune
     *
     * @return Commune
     */
    public function setIdCommune($idCommune)
    {
        $this->idCommune = $idCommune;

        return $this;
    }

    /**
     * Get idCommune
     *
     * @return int
     */
    public function getIdCommune()
    {
        return $this->idCommune;
    }

    /**
     * Set comCode
     *
     * @param string $comCode
     *
     * @return Commune
     */
    public function setComCode($comCode)
    {
        $this->comCode = $comCode;

        return $this;
    }

    /**
     * Get comCode
     *
     * @return string
     */
    public function getComCode()
    {
        return $this->comCode;
    }

    /**
     * Set comNom
     *
     * @param string $comNom
     *
     * @return Commune
     */
    public function setComNom($comNom)
    {
        $this->comNom = $comNom;

        return $this;
    }

    /**
     * Get comNom
     *
     * @return string
     */
    public function getComNom()
    {
        return $this->comNom;
    }

    /**
     * @return mixed
     */
    public function getObjetsApidae()
    {
        return $this->objetsApidae;
    }


}

