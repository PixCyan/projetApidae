<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;

class DefaultController extends Controller
{
    private $em;
    //0 = FR, 1 = EN
    private $lan = 0;

    public function indexAction()
    {
        $user = $this->getUser();
        $categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $suggestions = $this->em->getRepository(ObjetApidae::class)->findByObjSuggestion(1);
        return $this->render('ApidaeBundle:Default:index.html.twig', array('suggestions' => $suggestions,
            'categoriesMenu' => $categoriesMenu, 'langue' => $langue, 'user' => $user));
    }

    public function offreAction($id) {
        $user = $this->getUser();
        //phpinfo();
        //Test
        if($id == 0) {
            $id = 48925;
        }
        $categoriesMenu = $this->getCategoriesMenu();
        //Test affichage obet
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($id);
        $trad = $this->em->getRepository(TraductionObjetApidae::class)->findOneBy(
            array("objet"=> $objetApidae, "langue" => $langue));

        if($objetApidae != null) {
            return $this->render('ApidaeBundle:Default:vueFiche.html.twig',
                array('objet' => $objetApidae, 'trad' => $trad, 'langue' => $langue, 'categoriesMenu' => $categoriesMenu,
                    'user' => $user));
        } else {
            //TODO changer
            return $this->render('ApidaeBundle:Default:donnees.html.twig');
        }
    }

    public function listeAction($typeObjet, $categorieId)
    {
        $user = $this->getUser();
        //$categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

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

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet, 'categorie' => $categorie, 'user' => $user));
    }

    public function listeEvenementsAction($periode) {
        $user = $this->getUser();
        //$categoriesMenu = $this->getCategoriesMenu();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

        //TODO listeEvenement
        if($periode == 1) {
            $evenement = $this->em->getRepository(Evenement::class)->getAjourdhui();


        }

        $typeObjet = "Evénements";
        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet, 'user' => $user));
    }


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

    private function getCategoriesMenu() {
        $categories = array();
        $this->em = $this->getDoctrine()->getManager();
        $categories['Restaurants'] = $this->em->getRepository(Categorie::class)->getCategoriesRestaurants();
        $categories['Hébergements'] = $this->em->getRepository(Categorie::class)->getCategoriesHebergements();
        $categories['Activités'] = $this->em->getRepository(Categorie::class)->getCategoriesActivites();
        $categories['Evénements'] = $this->em->getRepository(Categorie::class)->getCategoriesEvenements();

        return $categories;
    }

}
