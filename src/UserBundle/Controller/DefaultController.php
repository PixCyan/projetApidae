<?php

namespace UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;
use Symfony\Component\HttpFoundation\Request;
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

    public function voirProfilAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        $categoriesMenu = $this->getCategoriesMenu();
        return $this->forward('FOSUserBundle:Profile:show', array(
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue));
    }

    public function voirPanierAction(Request $request) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        $categoriesMenu = $this->getCategoriesMenu();
        $user = $this->getUser();
        if($user != null) {
            $panier =$user->getPaniers();
        } else {
            $panier = null;
        }
        return $this->render('UserBundle:panier:listeSelections.html.twig', array(
            'categoriesMenu' => $categoriesMenu,
            'langue' => $langue,
            'panier' => $panier,
            'user' => $user));
    }

    public function listeUtilisateursAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
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
                $role = $form->get('roles')->getData();
                $user->setRoles($role);
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $user->setPassword($encoder->encodePassword($form->get('password')->getData(), $user->getSalt()));
                $em->merge($user);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'L\'utilisateur a bien été modifié.'
                );
                return $this->redirectToRoute('listeUsers');
            }
        }
        return $this->render('UserBundle:action:modifierUser.html.twig', array('form' => $form->createView(), 'user' => $user));
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
