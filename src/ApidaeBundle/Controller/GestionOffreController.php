<?php

namespace ApidaeBundle\Controller;


use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;
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

        //---- Test form
        if($trad != null) {
            $form = $this->createForm(new TraductionObjetApidaeType(), $trad);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //TODO changer
                $traductionObjet = $form->getData();

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
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'objet' => $objet, 'form' => $form->createView()));
    }

    public function gestionOffresAction() {
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $categoriesMenu = $this->getCategoriesMenu();
        $userCourant = $this->getUser();
        return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'user' => $userCourant));
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
