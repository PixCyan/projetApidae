<?php

namespace ApidaeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;

class DefaultController extends Controller
{
    private $em;

    public function indexAction()
    {

        //récupération des données :
        $apiKey = '4oqV1oVV';
        $projetId = '1464';
        $objId = '123457';
        $requete = array();
        $requete['apiKey'] = $apiKey;
        $requete['projetId'] = $projetId;
        $url = 'http://api.sitra-tourisme.com/api/v002/objet-touristique/get-by-id/';
        $url .= $objId;
        $url .= '?';
        $url .= 'apiKey='.urlencode($apiKey);
        $url .= '&projetId='.urlencode($projetId);
        //$url .= 'query='.urlencode(json_encode($requete));

        $content = file_get_contents($url);
        $data = json_decode($content);

        return $this->render('ApidaeBundle:Default:donnees.html.twig', array('url' => $url, 'data' => $data));
    }

    public function offreAction($id)
    {
        //phpinfo();
        if($id == 0) {
            $id = 48925;
        }
        //Test affichage obet
        $this->em = $this->getDoctrine()->getManager();
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($id);
        $trad = null;
        if($objetApidae != null) {
            return $this->render('ApidaeBundle:Default:vueFiche.html.twig', array('objet' => $objetApidae));
        } else {
            //TODO changer
            return $this->render('ApidaeBundle:Default:donnees.html.twig');
        }
    }

    public function listeAction($type)
    {
        //TODO
    }

}
