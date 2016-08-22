<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SejourPackage Cette classe regroupe et traite les informations concernant les objets touristiques de type "SEJOUR_PACKAGE"
 *
 * @ORM\Table(name="sejour_package")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\SejourPackageRepository")
 */
class SejourPackage extends ObjetApidae
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


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
     * Traites les informations du tableau donne pour definir les informations de l'objet
     * @param $tab
     */
    public function setCapacite($tab)
    {
        //void
    }
}

