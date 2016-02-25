<?php

namespace ApidaeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tarif
 *
 * @ORM\Table(name="tarifType")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\TarifTypeRepository")
 */
class TarifType
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
     * @ORM\Column(name="id_tarif", type="integer", length=255, unique=true)
     */
    private $idTarif;

    /**
     * @var int
     *
     * @ORM\Column(name="tarOrdre", type="integer", length=255)
     */
    private $ordre;

    /**
     * @var string
     *
     * @ORM\Column(name="tarLibelle", type="string", length=255)
     */
    private $tarLibelle;

    /**
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\InformationsTarif", mappedBy="tarifType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $infosTarif;

    public function __construct() {
        $this->infosTarif = new ArrayCollection();
    }

    /**
     * Ajoute/lie une infoTarif
     */
    public function addInfoTarif(InformationsTarif $t) {
        $this->infosTarif[] = $t;
    }

    /**
     * Supprime une infoTarif
     */
    public function removeInfoTarif(InformationsTarif $t) {
        $this->infosTarif->removeElement($t);
    }



    //---------------------- Getter & Setter ----------------------//

    /**
     * @return string
     */
    public function getTarLibelle()
    {
        return $this->tarLibelle;
    }

    /**
     * @param string $tarLibelle
     */
    public function setTarLibelle($tarLibelle)
    {
        $this->tarLibelle = $tarLibelle;
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
     * @return int
     */
    public function getIdTarif()
    {
        return $this->idTarif;
    }

    /**
     * @param int $idTarif
     */
    public function setIdTarif($idTarif)
    {
        $this->idTarif = $idTarif;
    }

    /**
     * @return mixed
     */
    public function getInfosTarif()
    {
        return $this->infosTarif;
    }

    /**
     * @param mixed $infosTarif
     */
    public function setInfosTarif($infosTarif)
    {
        $this->infosTarif = $infosTarif;
    }

    /**
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @param int $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }
}

