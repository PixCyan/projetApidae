<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Form\RechercheObjetForm;
use ApidaeBundle\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $em;
    //0 = FR, 1 = EN
    private $lan = 0;

    public function indexAction()
    {
        $user = $this->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $suggestions = $this->em->getRepository(ObjetApidae::class)->findByObjSuggestion(1);
        return $this->render('ApidaeBundle:Default:index.html.twig', array('suggestions' => $suggestions,
            'langue' => $langue, 'user' => $user));
    }

    public function offreAction($id) {
        $user = $this->getUser();
        //phpinfo();
        //Test
        if($id == 0) {
            $id = 48925;
        }
        //Test affichage obet
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($id);
        $trad = $this->em->getRepository(TraductionObjetApidae::class)->findOneBy(
            array("objet"=> $objetApidae, "langue" => $langue));

        if($objetApidae != null) {
            return $this->render('ApidaeBundle:Default:vueFiche.html.twig',
                array('objet' => $objetApidae, 'trad' => $trad, 'langue' => $langue,
                    'user' => $user));
        } else {
            //TODO changer
            return $this->render('ApidaeBundle:Default:donnees.html.twig');
        }
    }

    public function listeAction($typeObjet, $categorieId, Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        //$categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();

        //---- Add form
        $em = $this->getDoctrine()->getManager();
        $langue = $em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

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
            } else {
                $services = $this->getServicesFromObjets($objets);
                $session->set('listeObjets', $objets);
            }
            return $this->render('ApidaeBundle:Default:vueListe.html.twig',
                array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet,
                    'user' => $user, 'form' => $form->createView()));
        }

        //---- Fin forme recherche simple
        if($categorieId == '2883') {
            $categories = $this->em->getRepository(Categorie::class)->getHotels();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } elseif($categorieId == '2620') {
            $categories = $this->em->getRepository(Categorie::class)->getGites();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } elseif($categorieId == '2418') {
            $categories = $this->em->getRepository(Categorie::class)->getCampings();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } elseif($categorieId == '2646') {
            $categories = $this->em->getRepository(Categorie::class)->getHebergementsAutres();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } elseif($categorieId == '3404') {
            $categories = $this->em->getRepository(Categorie::class)->getBars();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } elseif($categorieId == '3203') {
            $categories = $this->em->getRepository(Categorie::class)->getMusees();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } elseif($categorieId == '3283') {
            $categories = $this->em->getRepository(Categorie::class)->getItineraires();
            $categorie =  $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            $objets = $this->traitementObjetsCategories($categories);
        } else {
            $categorie = $this->em->getRepository(Categorie::class)->findOneByCatId($categorieId);
            if(!$categorie) {
                //TODO ERROR
                throw $this->createNotFoundException('Cette categorie est vide.');
            } else {
                $objets = $categorie->getObjets();
            }
        }

        $session->set('listeObjets', $categorie->getObjets());
        $services = $this->getServicesFromObjets($categorie->getObjets());

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet, 'categorie' => $categorie,
                'user' => $user, 'form' => $form->createView(), 'services' => $services));
    }

    public function listeEvenementsAction($periode) {
        $user = $this->getUser();
        //$categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

        //TODO listeEvenement
        if($periode == 1) {
            $evenements = $this->em->getRepository(Evenement::class)->getAujourdhui2();
        } else {
            $evenements = $this->em->getRepository(Evenement::class)->getInterval($periode);
        }

        $typeObjet = "Evénements";
        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $evenements, 'langue' => $langue, 'typeObjet' => $typeObjet, 'user' => $user));
    }


    public function rechercheAffinneeAction(Request $request, $typeObjet) {
        $user = $this->getUser();
        $tab = $_POST['services'];
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        var_dump($tab);
        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $evenements, 'langue' => $langue, 'typeObjet' => $typeObjet, 'user' => $user));
    }

    /**
     * Retourne un tableau des objets auxquelles sont liées les categories données en param
     * @param $categories
     * @return ArrayCollection
     */
    private function traitementObjetsCategories($categories) {
        $objets = new ArrayCollection();
        foreach($categories as $category) {
            $c= $category->getObjets();
            foreach($c as $obj) {
                if(!$objets->contains($obj)) {
                    $objets->add($obj);
                }
            }
        }
        return $objets;
    }

    /**
     * Get tous les services liés aux objets de la liste actuelle
     * @param $rechercheActuelle
     * @return array
     */
    private function getServicesFromObjets($rechercheActuelle) {
        $services = array();
        foreach($rechercheActuelle as $objet) {
            foreach($objet->getServices() as $service) {
                if(!in_array($service, $services)) {
                    $services[] = $service;
                }
            }
        }
        return $services;
    }



}
