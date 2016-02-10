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
    private $objetLie;
    //l'objet lié

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjetLie()
    {
        return $this->objetLie;
    }

    public function setObjetLie(ObjetApidae $objet)
    {
        return $this->objetLie = $objet;
    }
}

