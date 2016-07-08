<?php

namespace ApidaeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ApidaeBundle\Entity\Panier;
use ApidaeBundle\Form\PanierType;

/**
 * Panier controller.
 *
 * @Route("/panier")
 */
class PanierController extends Controller
{
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

            return $this->redirectToRoute('voirPanier', array('id' => $panier->getId()));
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
        $categoriesMenu = $this->getCategoriesMenu();
        $user = $this->getUser();
        if($user != null) {
            $panier = $user->getPaniers();
        } else {
            $panier = null;
        }
        return $this->render('UserBundle:panier:listeSelections.html.twig', array(
            'categoriesMenu' => $categoriesMenu,
            'langue' => $langue,
            'panier' => $panier,
            'user' => $user));
    }


    /**
     * Finds and displays a Panier entity.
     */
    public function showAction(Request $request, $id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $panier = $em->getRepository('ApidaeBundle:Panier')->findOneBy(['id' => $id]);
        $deleteForm = $this->createDeleteForm($panier);

        return $this->render('panier/show.html.twig', array(
            'panier' => $panier,
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($panier);
            $em->flush();
        }

        return $this->redirectToRoute('panier_index');
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
}
