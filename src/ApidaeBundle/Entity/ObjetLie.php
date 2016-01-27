<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjetLie
 *
 * @ORM\Table(name="objet_lie")
 * @ORM\Entity(repositoryClass="ApidaeBundle\Repository\ObjetLieRepository")
 */
class ObjetLie
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
     * @ORM\ManyToOne(targetEntity="ApidaeBundle\Entity\ObjetApidae", inversedBy="objetsLies")
     * @ORM\JoinColumn(nullable = false)
     */
    private $objet;
    //id de l'objet auquel il est lié


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjet()
    {
        return $this->objet;
    }

    public function setObjet(ObjetApidae $objet)
    {
        return $this->objet = $objet;
    }
}

