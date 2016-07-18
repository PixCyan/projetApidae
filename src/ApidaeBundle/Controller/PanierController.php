<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ApidaeBundle\Entity\Panier;
use ApidaeBundle\Form\PanierType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panier controller.
 *
 * @Route("/panier")
 */
class PanierController extends Controller {
    private static $COUNT_COOKIE = 0;

    /**
     * Lists all Panier entities.
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $paniers = $em->getRepository('ApidaeBundle:Panier')->findAll();

        return $this->render('panier/index.html.twig', array(
            'paniers' => $paniers,
        ));
    }

    /**
     * Creates a new Panier entity.
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $panier = new Panier();

        //TODO cookie si user pas connecté
        $form = $this->createForm('ApidaeBundle\Form\PanierType', $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $panier = $form->getData();
            $panier->setUser($user);
            $em->persist($panier);
            $em->flush();

            return $this->redirectToRoute('voirUneSelection', array('id' => $panier->getId()));
        }

        return $this->render('panier/new.html.twig', array(
            'panier' => $panier,
            'form' => $form->createView(),
        ));
    }

    public function listePanierAction(Request $request) {
        //TODO liste de panier pour un USER
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        //$categoriesMenu = $this->getCategoriesMenu();
        $user = $this->getUser();
        if($user != null) {
            $paniers = $user->getPaniers();
        } else {
            $paniers = null;
        }
        return $this->render(':panier:index.html.twig', array(
            //'categoriesMenu' => $categoriesMenu,
            'langue' => $langue,
            'paniers' => $paniers,
            'user' => $user));
    }


    /**
     * Finds and displays a Panier entity.
     */
    public function showAction(Request $request, $id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository(Langue::class)->findOneBy(['lanShortCut' => ucwords($langue)]);
        if($user) {
            $panier = $em->getRepository('ApidaeBundle:Panier')->findOneBy(['id' => $id]);
        } else {
            $cookies = $request->cookies;
            if($cookies->has('apidaeSwad')) {
                $cookie = $cookies->get("apidaeSwad");
                $panier =  $panier = $em->getRepository(Panier::class)->findOneBy(['idCookie' => $cookie]);
            } else {
                $response = new Response();
                /*$cookie = array(
                    'name'  => 'apidaeSwad',
                    'value' => self::getCOUNTCOOKIE(),
                    'time'  => time() + 3600 * 24 * 7
                );*/
                $cookie = new Cookie('apidaeSwad', self::getCOUNTCOOKIE(), time() + 3600 * 24 * 7);
                self::setCOUNTCOOKIE(self::$COUNT_COOKIE++);
                $panier = new Panier();
                $panier->setIdCookie(self::getCOUNTCOOKIE());
                $panier->setpanLibelle("Favoris");
                $em->persist($panier);
                $em->flush();
                $response->headers->setCookie($cookie);
                $response->send();
            }
        }

        $deleteForm = $this->createDeleteForm($panier);

        return $this->render('panier/show.html.twig', array(
            'panier' => $panier,
            'objets' => $panier->getObjets(),
            'langue' => $langue,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Panier entity.
     */
    public function editAction(Request $request, Panier $panier)
    {
        $deleteForm = $this->createDeleteForm($panier);
        $editForm = $this->createForm('ApidaeBundle\Form\PanierType', $panier);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($panier);
            $em->flush();

            return $this->redirectToRoute('updatePanier', array('id' => $panier->getId()));
        }

        return $this->render('panier/edit.html.twig', array(
            'panier' => $panier,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Panier entity.
     */
    public function deleteAction(Request $request, Panier $panier)
    {
        $form = $this->createDeleteForm($panier);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->remove($panier);
        $em->flush();

        return $this->redirectToRoute('voirPaniers');
    }


    /**
     * Ajoute un objet au panier. $idPanier correspond à une sélection créée par l'utilisateur enregistré/connecté.
     * @param $idObjet
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ajouterObjetPanierAction(Request $request, $idObjet) {
        //TODO voir panier/cookie
        //$idPanier = -1, $idCookie = -1
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $objet = $em->getRepository(ObjetApidae::class)->findOneBy(['idObj' => $idObjet]);
        //Voir si utilisateur connecté
        if($user) {
            //ajout l'objet à la liste souhaitée
            $panier = $em->getRepository(Panier::class)->findOneBy(['id' => 1]);
            $objet->addPanier($panier);
            $panier->addObjet($objet);
            $em->merge($panier);
            $em->merge($objet);
            $em->flush();
        } else {
            $cookies = $request->cookies;
            if( $cookies->has('apidaeSwad')) {
                $cookie = $cookies->get("apidaeSwad");
                $panier =  $panier = $em->getRepository(Panier::class)->findOneBy(['idCookie' => $cookie]);
                $objet->addPanier($panier);
                $panier->addObjet($objet);
                $em->merge($panier);
                $em->merge($objet);
                $em->flush();
            } else {
                $response = new Response();
                /*$cookie = array(
                    'name'  => 'apidaeSwad',
                    'value' => self::getCOUNTCOOKIE(),
                    'time'  => time() + 3600 * 24 * 7
                );*/
                $cookie = new Cookie('apidaeSwad', self::getCOUNTCOOKIE(), time() + 3600 * 24 * 7);
                self::setCOUNTCOOKIE(self::$COUNT_COOKIE++);
                $panier = new Panier();
                $panier->setIdCookie(self::getCOUNTCOOKIE());
                $panier->setpanLibelle("Favoris");
                $em->persist($panier);
                $panier->addObjet($objet);
                $em->merge($objet);
                $em->flush();
                $response->headers->setCookie($cookie);
                $response->send();
            }
        }

        return $this->redirectToRoute('panier_index');

    }


    public function maSelectionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $cookies = $request->cookies;
        if($cookies->has('apidaeSwad')) {
            $cookie = $cookies->get("apidaeSwad");
            $panier =  $panier = $em->getRepository(Panier::class)->findOneBy(['idCookie' => $cookie]);
        } else {
            $response = new Response();
            /*$cookie = array(
                'name'  => 'apidaeSwad',
                'value' => self::getCOUNTCOOKIE(),
                'time'  => time() + 3600 * 24 * 7
            );*/
            $cookie = new Cookie('apidaeSwad', self::getCOUNTCOOKIE(), time() + 3600 * 24 * 7);
            self::setCOUNTCOOKIE(self::$COUNT_COOKIE++);
            $panier = new Panier();
            $panier->setIdCookie(self::getCOUNTCOOKIE());
            $panier->setpanLibelle("Favoris");
            $em->persist($panier);
            $em->flush();
            $response->headers->setCookie($cookie);
            $response->send();
        }
        return $this->redirectToRoute('voirUneSelection', array("id" => $panier->getId()));
    }

    /**
     * Creates a form to delete a Panier entity.
     *
     * @param Panier $panier The Panier entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Panier $panier)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('deletePanier', array('id' => $panier->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @return int
     */
    public static function getCOUNTCOOKIE()
    {
        return self::$COUNT_COOKIE;
    }

    /**
     * @param int $COUNT_COOKIE
     */
    public static function setCOUNTCOOKIE($COUNT_COOKIE)
    {
        self::$COUNT_COOKIE = $COUNT_COOKIE;
    }
}
