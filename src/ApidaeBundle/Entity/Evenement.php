<?php

namespace ApidaeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/** @Entity */
class Evenement extends ObjetApidae
{
    /**
     * @var string
     *
     * @ORM\Column(name="portee", type="string", nullable=true)
     */
    private $portee;

    /**
     * @var int
     *
     * @ORM\Column(name="ordrePortee", type="integer", nullable=true)
     */
    private $ordrePortee;

    /**
     * @return string
     */
    public function getPortee()
    {
        return $this->portee;
    }

    /**
     * @param string $portee
     */
    public function setPortee($portee)
    {
        $this->portee = $portee;
    }

    /**
     * @return int
     */
    public function getOrdrePortee()
    {
        return $this->ordrePortee;
    }

    /**
     * @param int $ordrePortee
     */
    public function setOrdrePortee($ordrePortee)
    {
        $this->ordrePortee = $ordrePortee;
    }


    public function setCapacite($tab)
    {
        $this->setPortee($tab['libelle']);
        $this->setOrdrePortee($tab['ordre']);
    }
}

