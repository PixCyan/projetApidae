<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\Service;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Form\RechercheObjetForm;
use ApidaeBundle\Fonctions\Fonctions;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if($id == 0) {
            $id = 48925;
        }
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($id);
        $trad = $this->em->getRepository(TraductionObjetApidae::class)->findOneBy(
            array("objet"=> $objetApidae, "langue" => $langue));

        if(!$objetApidae) {
            throw $this->createNotFoundException('Cette offre n\'existe pas.');
        } 
        return $this->render('ApidaeBundle:Default:vueFiche.html.twig',
            array('objet' => $objetApidae, 'trad' => $trad, 'langue' => $langue,
                'user' => $user));
    }

    public function rechercheSimpleAction(Request $request) {
        $session = $request->getSession();
        $user = $this->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

        //---- Add ----
        $recherche = str_replace(array ('<', '>', '.', ','), array ('&lt;', '&gt;', ' ', ' '),
            trim(strip_tags($request->query->get('champsRecherche'))));
        $keywords = array_unique(array_merge(explode(' ', $recherche), array ($recherche)));
        $objets = array();
        if(count($keywords) > 0) {
            $a_regexp = array();
            foreach ($keywords as $keyword) {
                if (mb_strlen($keyword) > 2)
                    $a_regexp[] = Fonctions::genererRegexp($keyword);
            }

            //--- Titre des offres :
            $i = 0;
            //var_dump($a_regexp);
            foreach($a_regexp as $regex) {
                $regex = "([^[:alpha:]]|$)" . $regex. " ";
                //print($regex);
                $res = $em->getRepository(ObjetApidae::class)->getObjetByNom($regex);
                //print (gettype($res));
                if($i+1 == count($a_regexp) && count($a_regexp) != 1) {
                    foreach ($res as $r) {
                        array_unshift($objets, $r);
                    }
                } else {
                    $objets = array_merge_recursive($objets, $res);
                }
                $i++;
            }

        }
        //------------


        if(empty($objets)) {
            $this->addFlash(
                'notice',
                'Aucun objet apidae ne correspond à votre recherche.'
            );
            $services = array();
        } else {
            $services = $this->getServicesFromObjets($objets);
            $session->set('listeObjets', $objets);
        }

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue,
                'typeObjet' => 'Recherche : '.end($keywords),
                'user' => $user, 'services' => $services));
    }

    public function listeAction($typeObjet, $categorieId, Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

        if($categorieId == '2734') {
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
                throw $this->createNotFoundException('Cette categorie est vide.');
            } else {
                $objets = $categorie->getObjets();
            }
        }



        //unset($_SESSION['listeObjets']);
        $session->remove('listeObjets');
        $session->set('listeObjets', $categorie->getObjets());

        $services = $this->getServicesFromObjets($objets);
        $modesPaiement = $this->getModesPaimentFromObjets($objets);
        $labelsQualite = $this->getClassementsFromObjets($objets);
        $tourismeAdapte = $this->getTourismeAdapteFromObjets($objets);

        print(count($modesPaiement));

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet, 'categorie' => $categorie,
                'user' => $user, 'services' => $services, 'modesPaiement' => $modesPaiement, 'labels' => $labelsQualite,
                'tourismeAdapte' => $tourismeAdapte));
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
        /*if($request->isXmlHttpRequest()) {
            pour l'ajax ici
        }*/

        $user = $this->getUser();
        $session = $request->getSession();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

        if($request->get('services')) {
            $services = $request->get('services');
        }
        if($request->get('classements')) {
            $classements = $request->get('classements');
        }

        $objetsRes = new ArrayCollection();

        //--- test
        //var_dump($services);
        if($liste = $session->get('listeObjets')) {
            foreach($liste as $objet) {
                //echo $objet->getIdObjet().'<br/>';
                if($services) {
                    foreach ($services as $service) {
                        $s = $this->em->getRepository(Service::class)->findOneBySerId($service);
                        echo 'lib = '.$s->getSerLibelle().'<br/>';
                        //var_dump($objet->getServices());
                        //TEMP
                        foreach($objet->getServices() as $value) {
                            if($value->getSerId() == $s->getSerId()) {
                                $objetsRes->add($objet);
                            }
                        }
                        /*if($objet->getServices()->contains($s)) {
                            $objetsRes->add($objet);
                        }*/
                    }
                }
            }
        }

        $session->remove('listeObjets');
        $session->set('listeObjets', $objetsRes);
        $services = $this->getServicesFromObjets($objetsRes);
        $modesPaiement = $this->getModesPaimentFromObjets($objetsRes);
        $labelsQualite = $this->getClassementsFromObjets($objetsRes);

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objetsRes, 'langue' => $langue, 'typeObjet' => $typeObjet, 'user' => $user,
                'services' => $services, 'modesPaiement' => $modesPaiement, 'labels' => $labelsQualite));
    }

    /**
     * Retourne un ArrayCollection des objets auxquelles sont liées les categories données en param
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
        $services = new ArrayCollection();
        foreach($rechercheActuelle as $objet) {
            foreach($objet->getServices() as $service) {
                //print("Ser = ".$service->getSerLibelle()." : ".$service->getSerId()."<br/>");
                if(!$services->contains($service) && ($service->getSerType() == "PrestationService")) {
                    $services->add($service);
                }
            }
        }
        return $services;
    }

    private function getModesPaimentFromObjets($rechercheActuelle) {
        $mp = new ArrayCollection();
        foreach($rechercheActuelle as $objet) {
            foreach($objet->getServices() as $service) {
                //print("Ser = ".$service->getSerLibelle()." : ".$service->getSerId()."<br/>");
                if(!$mp->contains($service) && ($service->getSerType() == "ModePaiement")) {
                    $mp->add($service);
                }
            }
        }
        return $mp;
    }

    private function getClassementsFromObjets($rechercheActuelle) {
        $lq = new ArrayCollection();
        foreach($rechercheActuelle as $objet) {
            foreach($objet->getLabelsQualite() as $label) {
                if(!$lq->contains($label)) {
                    $lq->add($label);
                }
            }
        }
        return $lq;
    }

    private function getTourismeAdapteFromObjets($rechercheActuelle) {
        $ta = new ArrayCollection();
        foreach($rechercheActuelle as $objet) {
            foreach($objet->getServices() as $handicap) {
                if(!$ta->contains($handicap) && ($handicap->getSerType() == "TourismeAdapte")) {
                    $ta->add($handicap);
                }
            }
        }
        return $ta;
    }

    
    


}
