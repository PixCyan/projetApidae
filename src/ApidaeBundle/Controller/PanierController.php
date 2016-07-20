<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public static $COUNT_COOKIE = 0;
    private static $lastPathShow;

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
        if($user) {
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
        } else {
            return $this->redirectToRoute('panier');
        }
    }


    /**
     * Finds and displays a Panier entity.
     */
    public function showAction(Request $request, $id)
    {
        echo $id;
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
                $panier =  $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $cookie]);
            } else {
                $response = new Response();
                /*$cookie = array(
                    'name'  => 'apidaeSwad',
                    'value' => self::getCOUNTCOOKIE(),
                    'time'  => time() + 3600 * 24 * 7
                );*/
                $tab = $this->setCookie();
                $panier = $tab['panier'];
                $response->headers->setCookie($tab['cookie']);
                $response->send();
            }
        }

        $deleteForm = $this->createDeleteForm($panier);

        return $this->render('panier/show.html.twig', array(
            'panier' => $panier,
            'objets' => $panier->getObjets(),
            'langue' => $langue,
            'user' => $user,
            'delete_form' => $deleteForm->createView()
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
            if(!$panier->getObjets()->contains($objet)) {
                $objet->addPanier($panier);
                $panier->addObjet($objet);
                $em->merge($panier);
                $em->merge($objet);
                $em->flush();
            }

        } else {
            $cookies = $request->cookies;
            if( $cookies->has('apidaeSwad')) {
                $cookie = $cookies->get("apidaeSwad");
                $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $cookie]);
                if(!$panier->getObjets()->contains($objet)) {
                    $objet->addPanier($panier);
                    $panier->addObjet($objet);
                    $em->merge($panier);
                    $em->merge($objet);
                    $em->flush();
                }

            } else {
                $response = new Response();
                /*$cookie = array(
                    'name'  => 'apidaeSwad',
                    'value' => self::getCOUNTCOOKIE(),
                    'time'  => time() + 3600 * 24 * 7
                );*/
                $tab = $this->setCookie();
                $panier = $tab['panier'];
                $response->headers->setCookie($tab['cookie']);
                $response->send();
            }
        }

        return $this->redirect(self::$lastPathShow);

    }

    public function ajouterTestAction(Request $request, $idObjet, $idPanier) {
        //TODO voir panier/cookie
        //$idPanier = -1, $idCookie = -1
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $objet = $em->getRepository(ObjetApidae::class)->findOneBy(['idObj' => $idObjet]);
        //Voir si utilisateur connecté
        if($user) {
            //ajout l'objet à la liste souhaitée
            $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $idPanier]);
            if(!$panier->getObjets()->contains($objet)){
                $objet->addPanier($panier);
                $panier->addObjet($objet);
                $em->merge($panier);
                $em->merge($objet);
                $em->flush();
            }
        } else {
            $cookies = $request->cookies;
            if( $cookies->has('apidaeSwad')) {
                $cookie = $cookies->get("apidaeSwad");
                $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $cookie]);
                if(!$panier->getObjets()->contains($objet)) {
                    $objet->addPanier($panier);
                    $panier->addObjet($objet);
                    $em->merge($panier);
                    $em->merge($objet);
                    $em->flush();
                }

            } else {
                $response = new Response();
                /*$cookie = array(
                    'name'  => 'apidaeSwad',
                    'value' => self::getCOUNTCOOKIE(),
                    'time'  => time() + 3600 * 24 * 7
                );*/
                $tab = $this->setCookie();
                $panier = $tab['panier'];
                $response->headers->setCookie($tab['cookie']);
                $response->send();
            }
        }

        return new Response();

    }

    /**
     * Retire un objet d'une liste de favoris (panier) donnée
     * @param Request $request
     * @param $idPanier
     * @param $idObjet
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function retirerListeAction(Request $request, $idPanier, $idObjet) {
        $em = $this->getDoctrine()->getManager();
        $objet = $em->getRepository(ObjetApidae::class)->findOneBy(['idObj' => $idObjet]);
        if($objet) {
            $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $idPanier]);
            if($panier) {
                $objet->removePanier($panier);
                $panier->removeObjet($objet);
                $em->merge($panier);
                $em->merge($objet);
                $em->flush();
            }
        }
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }


    public function maSelectionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $cookies = $request->cookies;
        if($cookies->has('apidaeSwad')) {
            $cookie = $cookies->get("apidaeSwad");
            $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $cookie]);
        } else {
            $response = new Response();
            $tab = $this->setCookie();
            $panier = $tab['panier'];
            $response->headers->setCookie($tab['cookie']);
            $response->send();
        }
        return $this->redirectToRoute('voirUneSelection', array("id" => $panier->getId()));
    }


    private function setCookie() {
        $em = $this->getDoctrine()->getManager();
        $panier = new Panier();
        $panier->setIdCookie(self::getCOUNTCOOKIE());
        $panier->setpanLibelle("Favoris");
        $em->persist($panier);
        $em->flush();

        $cookie = new Cookie('apidaeSwad', $panier->getId(), time() + 3600 * 24 * 7);
        self::setCOUNTCOOKIE(self::$COUNT_COOKIE++);
        return array('cookie' => $cookie, 'panier' => $panier);
    }


    //Controleur imbriqué -- plante
    public function getPanierCookieAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $cookies = $request->cookies;
        if($cookies->has('apidaeSwad')) {
            $cookie = $cookies->get('apidaeSwad');
            $panier = $em->getRepository(Panier::class)->findOneBy(['id' => $cookie]);
        } else {
            $response = new Response();

            $panier = new Panier();
            $panier->setIdCookie(self::getCOUNTCOOKIE());
            $panier->setpanLibelle("Favoris");
            $em->persist($panier);
            $em->flush();

            $cookie = new Cookie('apidaeSwad', $panier->getId(), time() + 3600 * 24 * 7);
            self::setCOUNTCOOKIE(self::$COUNT_COOKIE++);
            $response->headers->setCookie($cookie);
            $response->send();
        }
        return new Response($panier->getId());
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


    public function getPaniersJsonAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user) {
            $paniers = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
        } else {
            $cookies = $request->cookies;
            if($cookies->has('apidaeSwad')) {
                $cookie = $cookies->get("apidaeSwad");
                $paniers =  $em->getRepository(Panier::class)->findOneBy(['id' => $cookie]);
            } else {
                $response = new Response();
                /*$cookie = array(
                    'name'  => 'apidaeSwad',
                    'value' => self::getCOUNTCOOKIE(),
                    'time'  => time() + 3600 * 24 * 7
                );*/
                $panier = new Panier();
                $panier->setIdCookie(self::getCOUNTCOOKIE());
                $panier->setpanLibelle("Favoris");
                $em->persist($panier);
                $em->flush();

                $cookie = new Cookie('apidaeSwad', $panier->getId(), time() + 3600 * 24 * 7);
                self::setCOUNTCOOKIE(self::$COUNT_COOKIE++);
                $response->headers->setCookie($cookie);
                $response->send();
            }
        }
        $serializer = $this->container->get('jms_serializer');
        $paniers = $serializer->serialize($paniers, 'json');

        return (new JSONResponse())->setData(['paniers' => json_decode($paniers)]);

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
