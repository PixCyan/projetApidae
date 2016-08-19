<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SejourPackage
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


    public function setCapacite($tab)
    {
        //void
    }
}

