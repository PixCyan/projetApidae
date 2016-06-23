<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\LabelQualite;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\SelectionApidae;
use ApidaeBundle\Entity\Service;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Form\RechercheObjetForm;
use ApidaeBundle\Fonctions\Fonctions;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        if($request->getLocale()){
            $langue = $request->getLocale();
        } else {
            $langue = 'Fr';
        }

        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        $user = $this->getUser();

        $suggestions = $em->getRepository(ObjetApidae::class)->findByObjSuggestion(1);
        $response = $this->render('ApidaeBundle:Default:index.html.twig', array(
            'suggestions' => $suggestions,
            'langue' => $langue,
            'user' => $user));

        //$request->setLocale('en_En');

        return $response;
    }

    /**
     * Renvoie la fiche détaillée d'un objetApidae d'après son id
     * @param $id
     * @return Response
     */
    public function offreAction($id, Request $request) {
        $user = $this->getUser();
        //phpinfo();
        if($id == 0) {
            $id = 48925;
        }
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository(Langue::class)->findOneBy(['lanShortCut' => ucwords($langue)]);
        $objetApidae = $em->getRepository(ObjetApidae::class)->findOneByIdObj($id);
        $trad = $em->getRepository(TraductionObjetApidae::class)->findOneBy(
            array('objet'=> $objetApidae, 'langue' => $langue));

        if(!$objetApidae) {
            throw $this->createNotFoundException('Cette offre n\'existe pas.');
        } 
        return $this->render('ApidaeBundle:Default:vueFiche.html.twig',
            array('objet' => $objetApidae,
                'trad' => $trad,
                'langue' => $langue,
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
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);

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
            array('objets' => $objets,
                'langue' => $langue,
                'typeObjet' => 'Recherche : '.end($keywords),
                'user' => $user,
                'services' => $services));
    }

    /**
     * Affiche la liste de tous les objets d'une categorie donnée (Catégories définies par le menu)
     * @param $typeObjet
     * @param $categorieId
     * @param Request $request
     * @return Response
     */
    public function listeAction(Request $request, $typeObjet, $categorieId, Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        $selection = $em->getRepository(SelectionApidae::class)->findOneByIdSelectionApidae($categorieId);
        if(!$selection) {
            throw $this->createNotFoundException('Cette catégorie est vide.');
        }
        //$objets = $em->getRepository(ObjetApidae::class)->getObjetsByids($session->get('listeObjets'));
        $objets = $selection->getObjets();

        //unset($_SESSION['listeObjets']);
        $session->remove('listeObjets');
        $session->set('listeObjets', $this->getIdsObjetsFromObjets($objets));
        $session->remove('listeIntermediaire');

        $services = $this->getServicesFromObjets($objets);
        $modesPaiement = $this->getModesPaimentFromObjets($objets);
        $labelsQualite = $this->getClassementsFromObjets($objets);
        $tourismeAdapte = $this->getTourismeAdapteFromObjets($objets);

        if($typeObjet == "hebergements") {
            $typesHabitation = $this->getTypeHabitationFromObjets($objets);
        } else {
            $typesHabitation =[];
        }
  
        //var_dump($typesHabitation);

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets,
                'langue' => $langue,
                'typeObjet' => $typeObjet,
                'categorie' => $selection,
                'user' => $user,
                'services' => $services,
                'modesPaiement' => $modesPaiement,
                'labels' => $labelsQualite,
                'tourismeAdapte' => $tourismeAdapte,
                'typesHabitation' => $typesHabitation));
    }


    /**
     * Renvoie la liste de tous les objets "Evènement" selon la période donnée
     * @param $periode
     * @return Response
     */
    public function listeEvenementsAction(Request $request, $periode) {
        $user = $this->getUser();
        //$categoriesMenu = $this->getCategoriesMenu();
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);

        //TODO listeEvenement
        $eventRepository =  $em->getRepository(Evenement::class);

        $evenements = $periode == 1 ? $eventRepository->getAujourdhui2() : $eventRepository->getInterval($periode);
        
        $typeObjet = "Evénements";
        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $evenements,
                'langue' => $langue,
                'typeObjet' => $typeObjet,
                'user' => $user));
    }


    /**
     * Effectue une recherche d'apèrs les filtres cochés
     * @param Request $request
     * @param $typeObjet
     * @return Response
     */
    public function rechercheAffinneeAction(Request $request, $typeObjet, $categorieId) {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        //if($request->isXmlHttpRequest()) {
            //$session->remove('listeIntermediaire');
            //$session->remove('listeObjets');

            $idsFiltres = $session->get('idsFiltres');
            if(!$idsFiltres) {
                $idsFiltres = [];
                //$session->set('idsFiltres', $idsFiltres);
            }

            $objetsIds = $session->get('listeIntermediaire');
            if (is_array($objetsIds) && count($objetsIds) > 0) {
                $listeActuelle = $em->getRepository(ObjetApidae::class)->getObjetsByids($objetsIds);
            } else {
                $objetsIds = $session->get('listeObjets');
                if (is_array($objetsIds) && count($objetsIds) > 0) {
                    $listeActuelle = $em->getRepository(ObjetApidae::class)->getObjetsByids($objetsIds);
                } else {
                    $listeActuelle = [];
                }
            }

            if(!empty($listeActuelle)) {
                $serializer = $this->container->get('jms_serializer');
                /* Récupérer les objets qui sont liés à la categorie d'id ctaegorieId
                peuvent être soit categorie/service/labelQualite */
                $c = $em->getRepository(Categorie::class)->findOneByCatId($categorieId);
                if($c && $typeObjet == "categories") {
                    if(!in_array($c, $idsFiltres['categories'])) {
                        $idsFiltres['categorie'][] = $c;
                    }
                    $nouvelleListe = $this->traitementObjetsCategories($c, $listeActuelle, $typeObjet, $idsFiltres);
                    //print("categories");
                } else {
                    $s = $em->getRepository(Service::class)->findOneBySerId($categorieId);
                    if($s && $typeObjet == "services") {
                        if(!in_array($s, $idsFiltres['services'])) {
                            $idsFiltres['service'][] = $s;
                        }
                        $nouvelleListe = $this->traitementObjetsCategories($s, $listeActuelle, $typeObjet, $idsFiltres);
                        //print("services");
                    } else {
                        $l = $em->getRepository(LabelQualite::class)->findOneByLabId($categorieId);
                        if($l && $typeObjet == "classements") {
                            if(!in_array($c, $idsFiltres['classements'])) {
                                $idsFiltres['classement'][] = $l;
                            }
                            $nouvelleListe = $this->traitementObjetsCategories($l, $listeActuelle, $typeObjet, $idsFiltres);
                            //print("labels");
                        } else {
                            //print("else");
                            $nouvelleListe = [];
                        }
                    }
                }

                //$session->remove('listeObjets');
                $session->set('listeIntermediaire', $this->getIdsObjetsFromObjets($nouvelleListe));

                //Récupératino des données pour le traitement des filtres
                $services = $this->getServicesFromObjets($nouvelleListe);
                $modesPaiement = $this->getModesPaimentFromObjets($nouvelleListe);
                $classements = $this->getClassementsFromObjets($nouvelleListe);
                $categories = $this->getTypeHabitationFromObjets($nouvelleListe);
                $tourisme = $this->getTourismeAdapteFromObjets($nouvelleListe);

                //var_dump($session->get('listeObjets'));
                $objetsTableau = $serializer->serialize($nouvelleListe, 'json');
                $services = $serializer->serialize($services, 'json');
                $modesPaiement = $serializer->serialize($modesPaiement, 'json');
                $classements = $serializer->serialize($classements, 'json');
                $categories = $serializer->serialize($categories, 'json');
                $tourisme = $serializer->serialize($tourisme, 'json');

                //$langue = $request->getLocale();
                //$langueJson = '"langue":"'+$langue+'"';

                return (new JSONResponse())->setData([
                    'objets' => json_decode($objetsTableau),
                    'services' => json_decode($services),
                    'modesPaiements' => json_decode($modesPaiement),
                    'classements' => json_decode($classements),
                    'categories' => json_decode($categories),
                    'tourismesAdaptes' => json_decode($tourisme)]);
            } else {
                //TODO else
              return (new JSONResponse())->setData([]);
            }

        //}

    }

    /**
     * Retourne un ArrayCollection des objets auxquelles sont liées les categories données en param
     * @param $categorie
     * @param $objs
     * @return ArrayCollection
     */
    private function traitementObjetsCategories($categorie, $objs, $type, $idsFiltres) {
        $objets = new ArrayCollection();
        if($type == "categories") {
            foreach($objs as $o) {
                if($o->getCategories()->contains($categorie) && !$objets->contains($o)) {
                    $objets->add($o);
                }
            }
        } else if($type == "services") {
            foreach($objs as $o) {
                if($o->getServices()->contains($categorie) && !$objets->contains($o)) {
                    $objets->add($o);
                }
            }
        } else if($type == "classements") {
            foreach($objs as $o) {
                if($o->getLabelsQualite()->contains($categorie) && !$objets->contains($o)) {
                    $objets->add($o);
                }
            }
        }

        return $objets;
    }

    /**
     * Get tous les services liés aux objets de la liste actuelle
     * @param rechercheActuelle
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
     * Traite la requete en comparant ses résultats aux objets de la liste affichés (session)
     * Retourne un array des objets de la liste qui sont similaires à la requete
     * @param $objsRequete
     * @param $listeResActuelle
     * @return array
     */
    private function traitementRequeteForJson($objsRequete, $listeResActuelle) {
        $res = [];
        if($listeResActuelle) {
            $liste = new ArrayCollection($listeResActuelle);
            foreach($objsRequete as $value ) {
                if($liste->contains($value)) {
                    $res[] = $value;
                }
            }
        } else {
            $res = $listeResActuelle;
        }
        return $res;
    }

    public function tradTestAction($name, Request $request, $langue) {

        return $this->render('@Apidae/commun/test.html.twig', array(
            'name' => $name
        ));
    }

}

