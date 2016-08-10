<?php

namespace ApidaeBundle\Controller;


use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Fonctions\Fonctions;
use ApidaeBundle\Form\RechercheObjetForm;
use ApidaeBundle\Form\TraductionObjetApidaeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\Langue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * GestionOffre Controller
 *
 * Gestion des modifications des fiche objet Apidae
 * 
 * Class GestionOffreController
 * @package ApidaeBundle\Controller
 */
class GestionOffreController extends Controller
{

    /**
     * Modifie les informations d'un objet d'après son id
     *
     * @param $offreId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifierOffreAction($offreId, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);

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
        } else {
            $this->addFlash(
                'notice',
                'Erreur lors de la validation du formulaire de modification.'
            );
            $formTrad = null;
        }

        return $this->render('ApidaeBundle:GestionOffre:modifierOffre.html.twig', array(
           'langue' => $langue, 'objet' => $objet, 'form' => $formTrad->createView()));
    }

    /**
     * Recherche d'une offre d'après des mots clés
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gestionOffresAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        $userCourant = $this->getUser();

        $form = $this->createForm(new RechercheObjetForm());
        $form->handleRequest($request);

        //Gestion du formulaire de recherche
        if ($form->isSubmitted() && $form->isValid()) {
            //----- Changements
            $recherche = str_replace(array ('<', '>', '.', ','), array ('&lt;', '&gt;', ' ', ' '),
                trim(strip_tags($form->get('chaine')->getData())));
            $keywords = array_unique(array_merge(explode(' ', $recherche), array ($recherche)));
            $objets = array();
            if(count($keywords) > 0) {
                $a_regexp = array();
                foreach ($keywords as $keyword) {
                    if (mb_strlen($keyword) > 2)
                        $a_regexp[] = Fonctions::genererRegexp($keyword);
                }

                //--- Titre des offres :
                foreach($a_regexp as $regex) {
                    $regex = "([^[:alpha:]]|$)" . $regex. " ";
                    //print($regex);
                    $res = $em->getRepository(ObjetApidae::class)->getObjetByNom($regex);
                    array_merge($objets, $res);
                    //$objets = $res;
                }
            }
            //----------
            //$objets = $em->getRepository(ObjetApidae::class)->getObjetByNom($form->get('chaine')->getData());
            if($objets == null) {
                $this->addFlash(
                    'notice',
                    'Aucun objet apidae ne correspond à votre recherche.'
                );
            }
            return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
                'langue' => $langue, 'user' => $userCourant, 'objets' => $objets,
                'form' => $form->createView()));
        }
        return $this->render('ApidaeBundle:GestionOffre:gestionOffres.html.twig', array(
           'langue' => $langue, 'user' => $userCourant, 'form' => $form->createView()));
    }

}
