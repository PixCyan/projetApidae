<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Commune Cette classe regroupe et traite toutes les informations concernant les communes auxquelles sont rataches les
 * objets touristiques fournit par Apidae.
 *
 * @JMS\ExclusionPolicy("all")
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
     * ID Apidae de la commune
     * @var int
     *
     * @ORM\Column(name="comId", type="integer", unique=true)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $comId;

    /**
     * Code postal de la commune
     *
     * @var string
     *
     * @ORM\Column(name="comCode", type="string", length=255)
     */
    private $comCode;

    /**
     * Nom de la commune
     *
     * @var string
     *
     * @ORM\Column(name="comNom", type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $comNom;


    /**
     * Objets touristiques auxquels la commune est reliee
     *
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\ObjetApidae", mappedBy="commune")
     */
    private $objetsApidae;

    public function __construct() {
        //initialisation des collections
        $this->objetsApidae = new ArrayCollection();
    }

    /**
     * Ajoute/lie un objet Apidae à l'adresse
     * @param ObjetApidae $obj
     */
    public function addObjetApidae(ObjetApidae $obj) {
        $this->objetsApidae[] = $obj;
    }

    /**
     * Supprime un objet Apidae lié à l'adresse
     * @param ObjetApidae $obj
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
     * @return int
     */
    public function getComId()
    {
        return $this->comId;
    }

    /**
     * @param int $comId
     */
    public function setComId($comId)
    {
        $this->comId = $comId;
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

