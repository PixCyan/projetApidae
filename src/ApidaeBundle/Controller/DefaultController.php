<?php
namespace ApidaeBundle\Controller;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\LabelQualite;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\Multimedia;
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
    public function listeAction(Request $request, $typeObjet, $categorieId, $libelleCategorie)
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
        //Gestion des varibales de session
        $session->remove('listeObjets');
        $session->remove('listeIntermediaire');
        $idsObjets =  $this->getIdsObjetsFromObjets($objets);
        $session->set('listeObjets', $idsObjets);
        $session->remove('filtres');
        $filtres = [];
        $filtres["categories"] = [];
        $filtres["services"] = [];
        $filtres["classements"] = [];
        $filtres["paiements"] = [];
        $filtres["tourismes"] = [];
        $session->set('filtres', $filtres);
        $services = $this->getServicesFromObjets($objets);
        $modesPaiement = $this->getModesPaimentFromObjets($objets);
        $labelsQualite = $this->getClassementsFromObjets($objets);
        $tourismeAdapte = $this->getTourismeAdapteFromObjets($objets);
        if($typeObjet == "hebergements") {
            $typesHabitation = $this->getTypeHabitationFromObjets($objets);
        } else {
            $typesHabitation =[];
        }

        //TODO changer en donnant la trad dans BDD
        $explodeChaine = explode('_', $libelleCategorie);
        $categorieNom = "";
        $i = 0;
        foreach($explodeChaine as $chaine) {
            if($chaine != "_") {
                if($i == 0) {
                    $categorieNom .= ucwords($explodeChaine[$i]).' ';
                } else {
                    $categorieNom .= $explodeChaine[$i].' ';
                }
            }
            $i++;
        }

        $countPaiements = $em->getRepository(ObjetApidae::class)->getCountObjetHasServices($this->getIdsServices($modesPaiement), $selection->getIdSelectionApidae(), $idsObjets);
        $countServices = $em->getRepository(ObjetApidae::class)->getCountObjetHasServices($this->getIdsServices($services), $selection->getIdSelectionApidae(), $idsObjets);
        $countTourismes = $em->getRepository(ObjetApidae::class)->getCountObjetHasServices($this->getIdsServices($tourismeAdapte), $selection->getIdSelectionApidae(), $idsObjets);
        $countClassements = $em->getRepository(ObjetApidae::class)->getCountObjetHasLabels($this->getIdsClassements($labelsQualite), $selection->getIdSelectionApidae(), $idsObjets);
        $countCategories = $em->getRepository(ObjetApidae::class)->getCountObjetHasCategories($this->getIdsCategories($typesHabitation), $selection->getIdSelectionApidae(), $idsObjets);

        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $objets,
                'langue' => $langue,
                'typeObjet' => ucwords($typeObjet),
                'categorieNom' => $categorieNom,
                'user' => $user,
                'services' => $services,
                'modesPaiement' => $modesPaiement,
                'labels' => $labelsQualite,
                'tourismeAdapte' => $tourismeAdapte,
                'typesHabitation' => $typesHabitation,
                'idSelection' => $selection->getIdSelectionApidae(),
                'countPaiements' => $countPaiements,
                'countServices' => $countServices,
                'countTourisme' => $countTourismes,
                'countClassements' => $countClassements,
                'countCategories' => $countCategories));
    }
    /**
     * Renvoie la liste de tous les objets "Evènement" selon la période donnée
     * @param $periode
     * @return Response
     */
    public function listeEvenementsAction(Request $request, $periode) {
        $session = $request->getSession();
        $user = $this->getUser();
        //$categoriesMenu = $this->getCategoriesMenu();
        $em = $this->getDoctrine()->getManager();
        $langue = $request->getLocale();
        $langue = $em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
        //TODO listeEvenement
        $eventRepository =  $em->getRepository(Evenement::class);
        $evenements = $periode == 1 ? $eventRepository->getAujourdhui2() : $eventRepository->getInterval($periode);
        //Gestion des varibales de session
        $session->remove('listeObjets');
        $session->remove('listeIntermediaire');
        $session->remove('filtres');
        $filtres = [];
        $filtres["categories"] = [];
        $filtres["services"] = [];
        $filtres["classements"] = [];
        $filtres["paiements"] = [];
        $filtres["tourismes"] = [];
        $session->set('filtres', $filtres);

        $typeObjet = "Evénements";
        return $this->render('ApidaeBundle:Default:vueListe.html.twig',
            array('objets' => $evenements,
                'langue' => $langue,
                'typeObjet' => $typeObjet,
                'user' => $user));
    }

    /**
     * Effectue une recherche d'après les filtres cochés
     * @param Request $request
     * @param $typeObjet
     * @param $categorieId
     * @param $idSelection
     * @param $checked
     * @return Response
     * @throws \Exception
     * @internal param $option
     */
    public function rechercheAffinneeAction(Request $request, $typeObjet, $categorieId, $idSelection, $checked) {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        //print($typeObjet);
        if($checked == "false") {
            $filtres = $session->get('filtres');
            $objetsIds = $session->get('listeObjets');
            if (is_array($objetsIds) && count($objetsIds) > 0) {
                $listeActuelle = $em->getRepository(ObjetApidae::class)->getObjetsByids($objetsIds);
            } else {
                $listeActuelle = [];
            }

            if(!empty($listeActuelle)) {
                $serializer = $this->container->get('jms_serializer');
                $type = "decocher";

                if($typeObjet == "services" || $typeObjet == "paiements" || $typeObjet == "tourismes") {
                    $s = $em->getRepository(Service::class)->findOneBySerId($categorieId);
                    //print("ici");
                    if(isset($filtres[$typeObjet][$s->getSerId()])) {
                        unset($filtres[$typeObjet][$s->getSerId()]);
                    }
                } elseif ($typeObjet == "classements") {
                    $cl = $em->getRepository(LabelQualite::class)->findOneByLabId($categorieId);
                    if(isset($filtres[$typeObjet][$cl->getLabId()])) {
                        unset($filtres[$typeObjet][$cl->getLabId()]);
                    }
                } elseif($typeObjet == "categories") {
                    $c = $em->getRepository(Categorie::class)->findOneByCatId($categorieId);
                    if(isset($filtres[$typeObjet][$c->getCatId()])) {
                        unset($filtres[$typeObjet][$c->getCatId()]);
                    }
                }

                $nouvelleListe =  $this->getObjetsForAjax($type, $listeActuelle, $filtres, $idSelection);
                $session->set('filtres', $filtres);
                $session->set('listeIntermediaire', $this->getIdsObjetsFromObjets($nouvelleListe));

                $datas = $this->returnJsonData($nouvelleListe, $idSelection);

                return (new JSONResponse())->setData($datas);

            } else {
                //$this->redirectSelection($idSelection, $typeObjet);
                $em = $this->getDoctrine()->getManager();
                $sel = $em->getRepository(SelectionApidae::class)->findOneBy(['idSelectionApidae' => $idSelection]);
                $datas = $this->returnJsonData($sel->getObjets(), $idSelection);
                return (new JSONResponse())->setData($datas);

            }

        } else if($checked == "true"){
            $filtres = $session->get('filtres');
            $objetsIds = $session->get('listeObjets');
            if (is_array($objetsIds) && count($objetsIds) > 0) {
                $listeActuelle = $em->getRepository(ObjetApidae::class)->getObjetsByids($objetsIds);
            } else {
                $listeActuelle = [];
            }
            if(!empty($listeActuelle)) {
                /* Récupérer les objets qui sont liés à la categorie d'id ctaegorieId
                peuvent être soit categorie/service/labelQualite */
                $c = $em->getRepository(Categorie::class)->findOneByCatId($categorieId);
                if($c && ($typeObjet == 'categories')) {
                    if(!isset($filtres["categories"][$c->getCatId()])) {
                        $filtres['categories'][$c->getCatId()] = $c->getCatId();
                    }
                    $nouvelleListe = $this->getObjetsForAjax($typeObjet, $listeActuelle, $filtres, $idSelection);
                } else {
                    $s = $em->getRepository(Service::class)->findOneBySerId($categorieId);
                    if($s && ($typeObjet == "services" || $typeObjet == "paiements" || $typeObjet == "tourismes")) {
                        if($typeObjet == "paiements" && !isset($filtres["paiements"][$s->getSerId()])) {
                            $filtres["paiements"][$s->getSerId()] = $s->getSerId();

                        } elseif($typeObjet == "services" && !isset($filtres["services"][$s->getSerId()])) {
                            $filtres["services"][$s->getSerId()] = $s->getSerId();

                        } elseif ($typeObjet == "tourismes" && !isset($filtres["tourismes"][$s->getSerId()])) {
                            $filtres["tourismes"][$s->getSerId()] = $s->getSerId();
                        }
                        $nouvelleListe = $this->getObjetsForAjax($typeObjet, $listeActuelle, $filtres, $idSelection);
                        //print("services");
                    } else {
                        $l = $em->getRepository(LabelQualite::class)->findOneByLabId($categorieId);
                        if($l && $typeObjet == "classements") {
                            if(!isset($filtres["classements"][$l->getLabId()])) {
                                $filtres["classements"][$l->getLabId()] = $l->getLabId();
                            }
                            $nouvelleListe = $this->getObjetsForAjax($typeObjet, $listeActuelle, $filtres, $idSelection);
                        } else {
                            $nouvelleListe = [];
                        }
                    }
                }
                //$session->remove('listeObjets');
                $session->set('filtres', $filtres);
                $session->set('listeIntermediaire', $this->getIdsObjetsFromObjets($nouvelleListe));
                //Récupératino des données pour le traitement des filtres
                $datas = $this->returnJsonData($nouvelleListe, $idSelection);

                return (new JSONResponse())->setData($datas);
            } else {
                //$this->redirectSelection($idSelection, $typeObjet);
                $em = $this->getDoctrine()->getManager();
                $sel = $em->getRepository(SelectionApidae::class)->findOneBy(['idSelectionApidae' => $idSelection]);
                $datas = $this->returnJsonData($sel->getObjets(), $idSelection);
                return (new JSONResponse())->setData($datas);
            }
        } else if($checked == "reset") {
            //Réinitialise les filtres
            $filtres = [];
            $filtres["categories"] = [];
            $filtres["services"] = [];
            $filtres["classements"] = [];
            $filtres["paiements"] = [];
            $filtres["tourismes"] = [];
            $session->set('filtres', $filtres);

            $objetsIds = $session->get('listeObjets');
            if (is_array($objetsIds) && count($objetsIds) > 0) {
                $listeInitiale = $em->getRepository(ObjetApidae::class)->getObjetsByids($objetsIds);
            } else {
                $listeInitiale = [];
            }

            if(!empty($listeInitiale)) {
                $datas = $this->returnJsonData($listeInitiale, $idSelection);

                return (new JSONResponse())->setData($datas);
            } else {
                //$this->redirectSelection($idSelection, $typeObjet);
                $em = $this->getDoctrine()->getManager();
                $sel = $em->getRepository(SelectionApidae::class)->findOneBy(['idSelectionApidae' => $idSelection]);
                $datas = $this->returnJsonData($sel->getObjets(), $idSelection);
                return (new JSONResponse())->setData($datas);
            }

        }
        //}
    }

    /**
     * Renvoi un tableau de données JSON pour la requête Ajax
     * @param $nouvelleListe
     * @param $idSelection
     * @return array
     */
    private function returnJsonData($nouvelleListe, $idSelection) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');

        $services = $this->getServicesFromObjets($nouvelleListe);
        $modesPaiement = $this->getModesPaimentFromObjets($nouvelleListe);
        $classements = $this->getClassementsFromObjets($nouvelleListe);
        $categories = $this->getTypeHabitationFromObjets($nouvelleListe);
        $tourisme = $this->getTourismeAdapteFromObjets($nouvelleListe);

        $idsObjets = $this->getIdsObjetsFromObjets($nouvelleListe);

        $countPaiements = $em->getRepository(ObjetApidae::class)->getCountObjetHasServices($this->getIdsServices($modesPaiement), $idSelection, $idsObjets);
        $countServices = $em->getRepository(ObjetApidae::class)->getCountObjetHasServices($this->getIdsServices($services), $idSelection, $idsObjets);
        $countTourismes = $em->getRepository(ObjetApidae::class)->getCountObjetHasServices($this->getIdsServices($tourisme), $idSelection, $idsObjets);
        $countClassements = $em->getRepository(ObjetApidae::class)->getCountObjetHasLabels($this->getIdsClassements($classements), $idSelection, $idsObjets);
        $countCategories = $em->getRepository(ObjetApidae::class)->getCountObjetHasCategories($this->getIdsCategories($categories), $idSelection, $idsObjets);

        $objetsTableau = $serializer->serialize($nouvelleListe, 'json');
        $services = $serializer->serialize($services, 'json');
        $modesPaiement = $serializer->serialize($modesPaiement, 'json');
        $classements = $serializer->serialize($classements, 'json');
        $categories = $serializer->serialize($categories, 'json');
        $tourisme = $serializer->serialize($tourisme, 'json');

        return ['objets' => json_decode($objetsTableau),
            'services' => json_decode($services),
            'modesPaiements' => json_decode($modesPaiement),
            'classements' => json_decode($classements),
            'categories' => json_decode($categories),
            'tourismesAdaptes' => json_decode($tourisme),
            'countPaiements' => $countPaiements,
            'countServices' => $countServices,
            'countTourisme' => $countTourismes,
            'countClassements' => $countClassements,
            'countCategories' => $countCategories];
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
     * Retourne un ArrayCollection des objets auxquelles sont liées les categories/services/labels données en param
     * @param $type
     * @param $listeActuelle
     * @param $filtres
     * @param $selection
     * @return ArrayCollection
     * @internal param $categorie
     * @internal param $objs
     */
    public function getObjetsForAjax($type, $listeActuelle, $filtres, $selection) {
        $res = new ArrayCollection();
        $em = $this->getDoctrine()->getManager();
        $objetsActuelle = new ArrayCollection($listeActuelle);
        $res = $this->comparerServices($em, $filtres, $objetsActuelle, $selection, "services");
        $res = $this->checkResultat($res, $objetsActuelle);
        if($filtres["classements"]) {
            $res = $this->comparerClassements($em, $filtres, $res, $selection);
        }
        $res = $this->checkResultat($res, $objetsActuelle);
        if($filtres["categories"]) {
            $res = $this->comparerCategories($em, $filtres, $res, $selection);
        }
        $res = $this->checkResultat($res, $objetsActuelle);
        if($filtres["paiements"]) {
            $res = $this->comparerServices($em, $filtres, $res, $selection, "paiements");
        }
        $res = $this->checkResultat($res, $objetsActuelle);
        if($filtres["tourismes"]) {
            $res = $this->comparerServices($em, $filtres, $res, $selection, "tourismes");
        }
        $res = $this->checkResultat($res, $objetsActuelle);

        return $res;
    }

    /**
     * Renvoie la liste des objets correspondant à des services(paiement/service/tourisme adapté) et une sélection donnés
     * @param $em
     * @param $filtres
     * @param $objetsActuelle
     * @param $selection
     * @param $type
     * @return ArrayCollection
     */
    private function comparerServices($em, $filtres, $objetsActuelle, $selection, $type){
        $typeFiltre = [];
        if($type == "services") {
            $typeFiltre = $filtres["services"];
        } elseif($type == "paiements") {
            $typeFiltre = $filtres["paiements"];
        } elseif($type == "tourismes") {
            $typeFiltre = $filtres["tourismes"];
        }
        $objets = new ArrayCollection();
        $objets = new ArrayCollection($em->getRepository(ObjetApidae::class)->getObjetsServiceSelection($typeFiltre, $selection));
        $tmp = new ArrayCollection();
        foreach($objets as $o){
            if($objetsActuelle->contains($o) && !$tmp->contains($o)) {
                $tmp->add($o);
            }
        }
        return $tmp;
    }

    /**
     * Renvoie la liste des objets correspondant à des labels qualité et une sélection donnés
     * @param $em
     * @param $filtres
     * @param $objetsActuelle
     * @param $selection
     * @return ArrayCollection
     */
    private function comparerClassements($em, $filtres, $objetsActuelle, $selection) {
        $objets = new ArrayCollection();
        $objets = new ArrayCollection($em->getRepository(ObjetApidae::class)->getObjetsLabelsSelection($filtres["classements"], $selection));
        $tmp = new ArrayCollection();
        foreach($objets as $o){
            if($objetsActuelle->contains($o) && !$tmp->contains($o)) {
                $tmp->add($o);
            }
        }
        return $tmp;
    }

    /**
     * Renvoie la liste des objets correspondant à des categories et une sélection donnés
     * @param $em
     * @param $filtres
     * @param $objetsActuelle
     * @param $selection
     * @return ArrayCollection
     */
    private function comparerCategories($em, $filtres, $objetsActuelle, $selection) {
        $objets = new ArrayCollection();
        $objets = new ArrayCollection($em->getRepository(ObjetApidae::class)->getObjetsCategorieSelection($filtres["categories"], $selection));
        $tmp = new ArrayCollection();
        foreach($objets as $o){
            if($objetsActuelle->contains($o) && !$tmp->contains($o)) {
                $tmp->add($o);
            }
        }
        return $tmp;
    }

    /**
     * Renvoie une chaine traitée pour être passé dans l'url
     * (enlève les accents, gère les espaces...)
     * @param $chaine
     * @return mixed
     */
    private function traitementChaineUrl($chaine) {
        $str =  str_replace(",", "", str_replace(" ", "_", str_replace("'", "", $chaine)));
        //$str = strtr($str, 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
        //$str = strtr($str, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', 'aaaaaaceeeeiiiinooooouuuuyy');
        $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);

        return strtolower($str);
    }

    /**
     * Renvoie $res s'il n'est pas vide sinon renvoie la liste d'objets initiale
     * @param $res
     * @param $listeObjets
     * @return mixed
     */
    private function checkResultat($res, $listeObjets) {
        if(count($res) == 0) {
            return $listeObjets;
        } else {
            return $res;
        }
    }

    /**
     * Renvoie un tableau d'ids concernant les services donnés
     * @param $services
     * @return array
     */
    public function getIdsServices($services) {
        $res = [];
        foreach($services as $s) {
            $res[] = $s->getSerId();
        }
        return $res;
    }

    /**
     * Renvoie un tableau d'ids concernant les categories données
     * @param $categories
     * @return array
     */
    public function getIdsCategories($categories) {
        $res = [];
        foreach($categories as $s) {
            $res[] = $s->getCatId();
        }
        return $res;
    }

    /**
     * Renvoie un tableau d'ids concernant les labels qualité donnés
     * @param $classements
     * @return array
     */
    public function getIdsClassements($classements) {
        $res = [];
        foreach($classements as $s) {
        $res[] = $s->getLabId();
        }
        return $res;
    }

    private function traitementImage($url,$ids){
        $array = explode('/',$url);
        $name = array_pop($array);
        //print($name."<br>");
        $file = "/home/www/vhosts/swad.fr/apidae.swad.fr/web/bundles/apidae/imgApidae/";
        if(file_exists($file.$ids['idObj'])) {
            $this->copierImage($file.$ids['idObj']."/", $url);
        } else {
            mkdir($file . $ids['idObj']);
            $this->copierImage($file.$ids['idObj']."/", $url);
        }
    }

    private function copierImage($path, $url) {
        $array = explode('/',$url);
        $name = array_pop($array);

        if($this->urlExists($url) && !file_exists($path.$name)) {
            print("Là <br>");
            //$img = file_get_contents($url);
            //file_put_contents($path.$name, $img);
        }
    }

    private function urlExists($url){
        $headers=get_headers($url);
        return stripos($headers[0],"200 OK")?true:false;
    }

    public function testsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //print_r($request->getSession()->get('filtres'));
        $filtres = $request->getSession()->get('filtres');
        //$objs = new ArrayCollection($em->getRepository(ObjetApidae::class)->getObjetsByids($request->getSession()->get('listeIntermediaire')));

        //$objs = $em->getRepository(ObjetApidae::class)->getTest($this->getObjetsServ($filtres["services"]), 40518);

        $objet = $em->getRepository(ObjetApidae::class)->find(353);


        return $this->render('ApidaeBundle:Default:test.html.twig', array('objet' => $objet));
    }

}