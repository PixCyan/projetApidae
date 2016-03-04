<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;
use UserBundle\Entity\UserApidae;

class DefaultController extends Controller
{
    //0 = FR, 1 = EN
    private $lan = 0;

    public function indexAction()
    {
        return $this->render('UserBundle:Default:index.html.twig');
    }

    public function voirProfilAction() {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $categoriesMenu = $this->getCategoriesMenu();
        return $this->forward('FOSUserBundle:Profile:show', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue));
    }

    public function voirPanierAction() {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
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

    public function listeUtilisateursAction() {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $categoriesMenu = $this->getCategoriesMenu();
        $users = $em->getRepository(UserApidae::class)->findAll();
        $user = $this->getUser();
        return $this->render('UserBundle:Default:listeUsers.html.twig', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'users' => $users, 'user' => $user));
    }

    public function modifierUtilisateurAction($userId) {
        //TODO revoir
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        //$categoriesMenu = $this->getCategoriesMenu();
        $user = $em->getRepository(UserApidae::class)->findOneById($userId);
        $userCourant = $this->getUser();

        $formFactory = $this->get('fos_user.profile.form.factory');
        $form = $formFactory->createForm();
        $form->setData($user);

        return $this->render('FOSUserBundle:Profile:edit.html.twig', array('langue' => $langue, 'form' => $form->createView(),
            'userCourant' => $userCourant, 'user' => $user));
    }

    public function updateUtilisateurAction($userId) {
        //TODO revoir
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        //$categoriesMenu = $this->getCategoriesMenu();
        $user = $em->getRepository(UserApidae::class)->findOneById($userId);



        //return $this->render('FOSUserBundle:Profile:edit.html.twig', array('langue' => $langue, 'userCourant' => $userCourant ));
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
