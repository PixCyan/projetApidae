<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\TraductionObjetApidae;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;

class DefaultController extends Controller
{
    private $em;

    public function indexAction()
    {
        $categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue(0);
        $suggestions = $this->em->getRepository(ObjetApidae::class)->findByObjSuggestion(1);
        return $this->render('ApidaeBundle:Default:index.html.twig', array('suggestions' => $suggestions,
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue));
    }

    public function offreAction($id)
    {
        //phpinfo();
        //Test
        if($id == 0) {
            $id = 48925;
        }
        $categoriesMenu = $this->getCategoriesMenu();
        //Test affichage obet
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue(0);
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($id);
        $trad = $this->em->getRepository(TraductionObjetApidae::class)->findOneBy(
            array("objet"=> $objetApidae, "langue" => $langue));

        if($objetApidae != null) {
            return $this->render('ApidaeBundle:Default:vueFiche.html.twig',
                array('objet' => $objetApidae, 'trad' => $trad, 'langue' => $langue, 'categoriesMenu' => $categoriesMenu));
        } else {
            //TODO changer
            return $this->render('ApidaeBundle:Default:donnees.html.twig');
        }
    }

    public function listeAction($typeObjet, $categorieId)
    {
        $categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue(0);
        $categorie = $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
        $objets = $categorie->getObjets();

        if($objets != null) {
            return $this->render('ApidaeBundle:Default:vueListe.html.twig',
                array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet, 'categorie' => $categorie,
                    'categoriesMenu' => $categoriesMenu));
        } else {
            //TODO changer
            return $this->render('ApidaeBundle:Default:donnees.html.twig');
        }
    }


    private function getCategoriesMenu() {
        $categories = array();
        $this->em = $this->getDoctrine()->getManager();
        $categories['Restaurants'] = $this->em->getRepository(Categorie::class)->getCategoriesRestaurants();
        $categories['Hébergements'] = $this->em->getRepository(Categorie::class)->getCategoriesHebergements();
        $categories['Activités'] = $this->em->getRepository(Categorie::class)->getCategoriesActivites();
        $categories['Evénements'] = $this->em->getRepository(Categorie::class)->getCategoriesEvenements();

        return $categories;


    }

}
