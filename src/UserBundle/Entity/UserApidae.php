<?php

namespace UserBundle\Entity;

use ApidaeBundle\Entity\Panier;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="userApidae")
 */
class UserApidae extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="ApidaeBundle\Entity\Panier", mappedBy="user")
     * @ORM\JoinColumn(nullable=true)
     */
    private $paniers;

    public function __construct()
    {
        parent::__construct();
        $this->paniers = new ArrayCollection();
        // your own logic
    }

    /**
     * Ajoute/lie un objetApidae à la categorie
     */
    public function addTraduction(Panier $panier) {
        $this->paniers[] = $panier;

    }

    /**
     * Supprime objetApidae de la categorie
     */
    public function removeTraduction(Panier $panier) {
        $this->paniers->removeElement($panier);
    }

    /**
     * @return mixed
     */
    public function getPaniers()
    {
        return $this->paniers;
    }

}

