<?php

namespace UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\Entity\UserApidae;
use UserBundle\Form\UserApidaeType;


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

    public function updateUtilisateurAction($userId, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(UserApidae::class)->findOneById($userId);

        $form = $this->createForm(new UserApidaeType(), $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('password')->getData() !== $form->get('confirmerMdp')->getData()) {
                $this->addFlash(
                    'notice',
                    'Le mot de passe doit être identique !'
                );
            } else {
                $user = $form->getData();
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $user->setPassword($encoder->encodePassword($form->get('password')->getData(), $user->getSalt()));
                $em->merge($user);
                $em->flush();
                return $this->redirectToRoute('confirmerModifUser');
            }
        }
        return $this->render('UserBundle:action:modifierUser.html.twig', array('form' => $form->createView(), 'user' => $user));
    }

    public function confirmerModifUserAction() {
        //TODO créer vue
        return new Response("Utilisateur modifié.");
    }

    public function deleteUserAction($userId) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(UserApidae::class)->findOneById($userId);
        $em->remove($user);
        $em->flush();

        $this->addFlash(
            'notice',
            'L\'utilisateur a bien été supprimé.'
        );


        return $this->redirectToRoute('listeUsers');
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
