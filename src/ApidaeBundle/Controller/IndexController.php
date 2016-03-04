<?php

namespace ApidaeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;

class IndexController extends Controller
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
