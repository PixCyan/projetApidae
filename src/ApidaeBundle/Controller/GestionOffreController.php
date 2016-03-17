<?php

namespace ApidaeBundle\Controller;


use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Form\RechercheObjetForm;
use ApidaeBundle\Form\TraductionObjetApidaeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class GestionOffreController extends Controller
{
    //0 = FR, 1 = EN
    private $lan = 0;

    public function modifierOffreAction($offreId, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $categoriesMenu = $this->getCategoriesMenu();
        //TODO getOffre


        $objet = $em->getRepository(ObjetApidae::class)->findOneByIdObj($offreId);
        $trad = $em->getRepository(TraductionObjetApidae::class)->findOneBy(
            array("objet"=> $objet, "langue" => $langue));

        if ($trad != null) {
            $formTrad = $this->createForm(new TraductionObjetApidaeType(), $trad);
            $formTrad->handleRequest($request);
            if ($formTrad->isSubmitted() && $formTrad->isValid()) {
                $traductionObjet = $formTrad->getData();
                $em->merge($traductionObjet);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'L\'objet Apidae a bien été modifiée.'
                );
                return $this->redirectToRoute('gestionOffres');
            }

        }
        return $this->render('ApidaeBundle:GestionOffre:modifierOffre.html.twig', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'objet' => $objet, 'form' => $formTrad->createView()));
    }

    public function gestionOffresAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $categoriesMenu = $this->getCategoriesMenu();
        $userCourant = $this->getUser();

        $form = $this->createForm(new RechercheObjetForm());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //TODO changer
            $objets = $em->getRepository(ObjetApidae::class)->getObjetByNom($form->get('chaine')->getData());
            if($objets == null) {
                $this->addFlash(
                    'notice',
                    'Aucun objet apidae ne correspond à votre recherche.'
                );
            }
            return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
                'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'user' => $userCourant, 'objets' => $objets,
                'form' => $form->createView()));
        }
        return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'user' => $userCourant, 'form' => $form->createView()));
    }

    public function rechercheOffreAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $categoriesMenu = $this->getCategoriesMenu();
        $userCourant = $this->getUser();
        $form = $this->createForm(new RechercheObjetForm());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //TODO changer
            $objets = $em->getRepository(ObjetApidae::class)->getObjetByNom($form->get('chaine')->getData());
            if($objets == null) {
                $this->addFlash(
                    'notice',
                    'Aucun objet apidae ne correspond à votre recherche.'
                );
            }
            return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
                'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'user' => $userCourant, 'objets' => $objets,
                'form' => $form));
        }
        return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'user' => $userCourant, 'form' => $form));
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
