<?php

namespace ApidaeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;

class IndexController extends Controller
{
    private $em;
    /**
     * @Route("/")
     */
    public function indexAction()
    {
		//Test objetLie
        $this->em = $this->getDoctrine()->getManager();
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj(119889);
        $trad = null;
        if($objetApidae != null) {
            $traductions = $objetApidae->getTraductions();
            foreach($traductions as $value) {
                if($value->getLangue()->getLanLibelle() == "Français") {
                    $trad = $value;
                }
            }
            return $this->render('ApidaeBundle:Default:index.html.twig', array('objet' => $objetApidae, 'trad' => $trad));
        } else {
            return $this->render('ApidaeBundle:Default:index.html.twig');
        }




    }
}
