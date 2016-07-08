<?php
/**
 * Created by PhpStorm.
 * User: Nadia
 * Date: 08/07/2016
 * Time: 15:23
 */

namespace ApidaeBundle\Controller;
use ApidaeBundle\Entity\Panier;
use ApidaeBundle\Form\RechercheObjetForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {
    private static $COUNT_COOKIE = 0;


    /**
     * Ajoute un objet au panier. $idPanier correspond à une sélection créée par l'utilisateur enregistré/connecté.
     * @param $idObjet
     * @param int $idPanier
     * @param int $idCookie
     */
    public function ajouterObjetPanier($idObjet, $idPanier = -1, $idCookie = -1) {
        $em = $this->getDoctrine()->getManager();

        //Voir si utilisateur connecté

    }

    /**
     * Créer une sélection (un panier)
     * @param $nom
     */
    public function creerSelection($nom) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $panier = new Panier();
        $panier->setpanLibelle($nom);
        $panier->setUser($user);

    }








}