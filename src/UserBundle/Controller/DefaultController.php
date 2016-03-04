<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;

class DefaultController extends Controller
{

    public function indexAction()
    {
        return $this->render('UserBundle:Default:index.html.twig');
    }

    public function voirProfilAction() {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue(0);
        $categoriesMenu = $this->getCategoriesMenu();
        return $this->forward('FOSUserBundle:Profile:show', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue));
    }

    public function voirPanierAction() {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue(0);
        $categoriesMenu = $this->getCategoriesMenu();
        $user = $this->getUser();
        if($user != null) {
            $panier =$user->getPaniers();
        } else {
            $panier = null;
        }
        return $this->render('UserBundle:panier:listeSelections.html.twig', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'panier' => $panier));
    }

    private function getCategoriesMenu() {
        $categories = array();
        $em = $this->getDoctrine()->getManager();
        $categories['Restaurants'] = $em->getRepository(Categorie::class)->getCategoriesRestaurants();
        $categories['Hébergements'] = $em->getRepository(Categorie::class)->getCategoriesHebergements();
        $categories['Activités'] = $em->getRepository(Categorie::class)->getCategoriesActivites();
        $categories['Evénements'] = $em->getRepository(Categorie::class)->getCategoriesEvenements();

        return $categories;
    }
}
