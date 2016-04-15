<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\LabelQualite;
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

    /**
     * Renvoi la page d'accueil avec les suggestions
     * @return Response
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);
        $suggestions = $this->em->getRepository(ObjetApidae::class)->findByObjSuggestion(1);
        return $this->render('ApidaeBundle:Default:index.html.twig', array('suggestions' => $suggestions,
            'langue' => $langue, 'user' => $user));
    }

    /**
     * Renvoie la fiche détaillée d'un objetApidae d'après son id
     * @param $id
     * @return Response
     */
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

    /**
     * Effectue une recherche d'après des mots clés donnés dans la barre de recherche
     * @param Request $request
     * @return Response
     */
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
            $session->set('listeObjets', $this->getIdsObjetsFromObjets($objets));
        }

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue,
                'typeObjet' => 'Recherche : '.end($keywords),
                'user' => $user, 'services' => $services));
    }

    /**
     * Renvoie la liste de tous les objets d'une categorie donnée (Catégories définies par le menu)
     * @param $typeObjet
     * @param $categorieId
     * @param Request $request
     * @return Response
     */
    public function listeAction($typeObjet, $categorieId, Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $langue = $this->em->getRepository(Langue::class)->findOneByCodeLangue($this->lan);

        // Ancien traitement
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

        /*
        $jsonMenu = file_get_contents("/var/www/local/Symfony/projetApidae/tools/donneesMenu.json");
        $donneesMenu = json_decode($jsonMenu);
        foreach($donneesMenu as $value) {


        }*/

        //unset($_SESSION['listeObjets']);
        $session->remove('listeObjets');
        $session->set('listeObjets', $this->getIdsObjetsFromObjets($objets));

        $services = $this->getServicesFromObjets($objets);
        $modesPaiement = $this->getModesPaimentFromObjets($objets);
        $labelsQualite = $this->getClassementsFromObjets($objets);
        $tourismeAdapte = $this->getTourismeAdapteFromObjets($objets);
        if($typeObjet == "Hebergements") {
            $typesHabitation = $this->getTypeHabitationFromObjets($objets);
        } else {
            $typesHabitation =[];
        }

        //var_dump($typesHabitation);

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets, 'langue' => $langue, 'typeObjet' => $typeObjet, 'categorie' => $categorie,
                'user' => $user, 'services' => $services, 'modesPaiement' => $modesPaiement, 'labels' => $labelsQualite,
                'tourismeAdapte' => $tourismeAdapte, 'typesHabitation' => $typesHabitation));
    }


    /**
     * Renvoie la liste de tous les objets "Evènement" selon la période donnée
     * @param $periode l'interval entre deux dates
     * @return Response
     */
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


    /**
     * Effectue une recherche d'apèrs les filtres cochés
     * @param Request $request
     * @param $typeObjet
     * @return Response
     */
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
        } else{
            $services = [];
        }
        if($request->get('classements')) {
            $classements = $request->get('classements');
        } else {
            $classements = [];
        }
        if($request->get('handicaps')) {
            $handicaps = $request->get('handicaps');
        } else {
            $handicaps = [];
        }
        if($request->get('categories')) {
            $categories = $request->get('categories');
        } else {
            $categories = [];
        }

        $objetsRes = new ArrayCollection();

        //--- test
        //var_dump($services);
        if($liste = $this->getObjetsFromIdsObjets($session->get('listeObjets'))) {
            foreach($liste as $objet) {
                //echo $objet->getIdObjet().' libelle :  '.$objet->getNom().'<br/>';
                if($services) {
                    foreach ($services as $service) {
                        $s = $this->em->getRepository(Service::class)->findOneBySerId($service);
                        if($objet->getServices()->contains($s) && !$objetsRes->contains($objet)) {
                            $objetsRes->add($objet);
                        }
                    }
                }
                if($classements) {
                    foreach ($classements as $classement) {
                        $c = $this->em->getRepository(LabelQualite::class)->findOneByLabId($classement);
                        //echo 'lib = '.$s->getSerLibelle().'<br/>';
                        if($objet->getLabelsQualite()->contains($c) && !$objetsRes->contains($objet)) {
                            $objetsRes->add($objet);
                        }
                    }
                }
                if($handicaps) {
                    foreach ($handicaps as $handicap) {
                        $s = $this->em->getRepository(Service::class)->findOneBySerId($handicap);
                        if($objet->getServices()->contains($s) && !$objetsRes->contains($objet)) {
                            $objetsRes->add($objet);
                        }
                    }
                }
                if($categories) {
                    foreach ($categories as $categorie) {
                        $c = $this->em->getRepository(Categorie::class)->findOneBySerId($categorie);
                        if($objet->getCategories()->contains($c) && !$objetsRes->contains($objet)) {
                            $objetsRes->add($objet);
                        }
                    }
                }
            }
        }

        $session->remove('listeObjets');
        $session->set('listeObjets', $this->getIdsObjetsFromObjets($objetsRes));
        $services = $this->getServicesFromObjets($objetsRes);
        $modesPaiement = $this->getModesPaimentFromObjets($objetsRes);
        $labelsQualite = $this->getClassementsFromObjets($objetsRes);
        if($typeObjet == "Hebergements") {
            $typesHabitation = $this->getTypeHabitationFromObjets($objetsRes);
        } else {
            $typesHabitation =[];
        }

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objetsRes, 'langue' => $langue, 'typeObjet' => $typeObjet, 'user' => $user,
                'services' => $services, 'modesPaiement' => $modesPaiement, 'labels' => $labelsQualite,
                'typesHabitation' => $typesHabitation));
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

    /**
     * Get tous les modes de paiements liés aux objts de la liste actuelle
     * @param $rechercheActuelle
     * @return ArrayCollection
     */
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

    /**
     * Get tous les classements (labels qualité) liés aux objets de la liste donnée
     * @param $rechercheActuelle
     * @return ArrayCollection
     */
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

    /**
     * Get tous les services de tourisme adapté liés aux objets de la liste donnée
     * @param $rechercheActuelle
     * @return ArrayCollection
     */
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

    /**
     * Retourne un tableau de categories liés à la liste d'objets passé en paramètre et dont le type de categorie est "TypeHabitation"
     * @param $rechercheActuelle
     * @return ArrayCollection
     */
    private function getTypeHabitationFromObjets($rechercheActuelle) {
        $typeHabitation = new ArrayCollection();
        foreach($rechercheActuelle as $objet) {
            foreach($objet->getCategories() as $cat) {
                if(!$typeHabitation->contains($cat) && ($cat->getCatRefType() == "TypeHabitation")) {
                    $typeHabitation->add($cat);
                }
            }
        }
        return $typeHabitation;
    }

    /**
     * Retourne un tableau d'id d'après un tableau d'objets Apidae
     * @param $objets
     * @return array
     */
    private function getIdsObjetsFromObjets($objets) {
        $idsObjets = [];
        foreach ($objets as $value) {
            $idsObjets[] = $value->getIdObjet();
        }
        return $idsObjets;
    }

    /**
     * Retourne un tableau d'objets Apidae d'après un tableau d'IDs
     * @param $idsObjets
     * @return array
     */
    private function getObjetsFromIdsObjets($idsObjets) {
        $objets = [];
        foreach($idsObjets as $value) {
            $o = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($value);
            $objets[] = $o;
        }
        return $objets;
    }
}
