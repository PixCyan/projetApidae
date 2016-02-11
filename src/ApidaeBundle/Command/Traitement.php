<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\ObjetLie;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ApidaeBundle\Entity\SelectionApidae;
use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Entity\LabelQualite;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Commune;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\Equipement;
use ApidaeBundle\Entity\Service;
use ApidaeBundle\Entity\MoyenCommunication;
use ApidaeBundle\Entity\Multimedia;
use ApidaeBundle\Entity\Tarif;
use ApidaeBundle\Entity\Ouverture;
use ApidaeBundle\Entity\TypePublic;

define('SIT_LANGUE', 'fr');

class Traitement extends ContainerAwareCommand {
    private $em;
    private $communes;
    private $fichierRef;

    // …
    protected function configure() {
        $this
            ->setName('command:traitement')
            ->setDescription('Traitement des données Apidae');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //$this->em = $this->getDoctrine()->getManager();
        $this->em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
        $languesSite[0] = "Français";
        $languesSite[1] = "English";

        //Récupération fichiers :
       try {
           $export = file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/selections.json");
           $this->communes = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/communes.json"));
           $this->fichierRef = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/elements_reference.json"));
           $selections_data = json_decode($export);
           foreach ($selections_data as $value) {
               $selectionApidae = $this->em->getRepository(SelectionApidae::class)->findOneBySelLibelle($value->libelle->libelleFr);
               if ($selectionApidae == null) {
                   $selectionApidae = new SelectionApidae();
                   $selectionApidae->setIdSelectionApidae($value->id);
                   $selectionApidae->setSelLibelle($value->nom);
                   $this->em->persist($selectionApidae);
                   $this->em->flush();
               } else {
                   print($selectionApidae->getSelLibelle() . "\n");
                   $this->em->merge($selectionApidae);
               }
               foreach ($value->objetsTouristiques as $val) {
                   print($val->id . "\n");
                   //=> $data = aller chercher le bon fichier dans objetsModifies
                   $data = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/objets_modifies/objets_modifies-" . $val->id . ".json"));

                   //récupération des données :
                   //Traitement de la chaine "type" (pour récupération d'info : notation différente selon le typeApidae)
                   $type = $data->type;
                   $chaineExplode = explode("_", $type);
                   $tab = null;
                   foreach ($chaineExplode as $value) {
                       $str = strtolower($value);
                       $str[0] = strtoupper($str[0]);
                       $tab[] = $str;
                   }
                   $typeObj = implode($tab);
                   $chaineInformations = "informations" . $typeObj;
                   if ($data->type == "FETE_ET_MANIFESTATION") {
                       $chaineType = "typesManifestation";
                   } else {
                       $tab[0] = strtolower($tab[0]);
                       $chaineType = implode($tab) . "Type";
                   }
                   $this->traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite, $typeObj);
               }
           }
           //---
           $output->writeln("Fin de traitement.");
           return $this->render('ApidaeBundle:Default:traitement.html.twig', array('url' => $data->$chaineInformations));
       } catch(Exception $e) {
           $output->writeln("Problème : ".$e->getMessage());
       }
    }

    private function traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite, $typeObj) {
        //-------------------- ObjetApidae ----------------------
        $objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($data->id);
        if($objetApidae == null) {
            $objetApidae = new ObjetApidae();
            $objetApidae->setIdObjet($data->id);
            if(isset($data->geolocalisation)) {
                $geo = $data->geolocalisation;
                //TODO formater geolocalisation
                //$objetApidae->setObjGeolocalisation(isset($geo->geoJson->coordinates));
                $objetApidae->setObjGeolocalisation(null);
            } else {
                $objetApidae->setObjGeolocalisation(null);
            }
            $objetApidae->setObjSuggestion(false);
            $objetApidae->setObjDateSuggestion(null);
            $objetApidae->setObjTypeApidae($data->type);
            $objetApidae->addSelectionApidae($selectionApidae);
            $selectionApidae->addObjetApidae($objetApidae);
        }

        //--------------------Langue ----------------------
        $i = 0;
        $langueTrad= "";
        foreach($languesSite as $key => $value) {
            $shortCut = $value[0] . $value[1];
            $lan = $this->em->getRepository(Langue::class)->findOneByLanLibelle($value);
            if($lan == null) {
                $langue = new Langue();
                $langue->setCodeLangue($i);
                $langue->setLanLibelle($value);
                $langue->setLanShortCut($shortCut);
                $langue->setLanIso("?");
                $this->em->persist($langue);
                $langueTrad = $langue;
            } else {
                $langueTrad = $lan;
            }
            $chaineLangue = "libelle".$shortCut;

            //-------------------- Categories ----------------------
            //Récupération de la/des catégorie(s)
            if(isset($data->$chaineInformations->$chaineType->libelleFr)) {
                //Accès à libelle "simpelement"
                $cat = $data->$chaineInformations->$chaineType->libelleFr;
                $this->traitementCategorie($cat, $objetApidae);
            } else if (isset($data->$chaineInformations->categories[0]->id)){
                foreach($this->fichierRef as $v) {
                    //print("Cat = ".$typeObj."Categorie. id = ".$data->$chaineInformations->categories[0]->id);
                    if($v->elementReferenceType == $typeObj."Categorie"
                        && $v->id == $data->$chaineInformations->categories[0]->id) {
                        $this->traitementCategorie($v->$chaineLangue, $objetApidae);
                    }
                    if($v->elementReferenceType == "FeteEtManifestationType"
                        && isset($data->$chaineInformations->typesManifestation[0]->id)
                        && $v->id == $data->$chaineInformations->typesManifestation[0]->id) {
                        $this->traitementCategorie($v->$chaineLangue, $objetApidae);
                    }
                    if(isset($data->$chaineInformations->categories[0]->familleCritere)) {
                        $famille = $data->$chaineInformations->categories[0]->familleCritere->$chaineLangue;
                        $this->traitementCategorie($famille, $objetApidae);
                    }
                }
            }

            //-------------------- Adresse - Communes ----------------------
            if($adr = $data->localisation->adresse) {
                $objetApidae->setCodePostal($adr->codePostal);
                if(isset($adr->commune->id)) {
                    $commune = $this->em->getRepository(Commune::class)->findOneByComId($adr->commune->id);
                    if($commune != null) {
                        $commune->addObjetApidae($objetApidae);
                        $objetApidae->setCommune($commune);
                        //update
                        $this->em->merge($commune);
                    } else {
                        $commune = new Commune();
                        foreach($this->communes as $com) {
                            if($com->id == $adr->commune->id) {
                                $commune->setComCode($com->code);
                                $commune->setComNom($com->nom);
                                $commune->setComId($com->id);
                                break;
                            }
                        }
                        //nouvelle entrée
                        $this->em->persist($commune);
                    }
                }
                for($i = 1; $i < 5; $i++) {
                    $chaine = "adresse".$i;
                    if(isset($adr->$chaine)) {
                        $objetApidae->setAdresse($adr->$chaine);
                        break;
                    } else {
                        $objetApidae->setAdresse(" ");
                    }
                }
            }
            $this->em->merge($objetApidae);
            $this->em->flush();

            //------------------------------------------------ Traduction --------------------------------------------------
            $traduction = $this->em->getRepository(TraductionObjetApidae::class)->findOneByTraNom($data->nom->$chaineLangue);
            if($traduction != null && ($traduction->getLangue()->getLanLibelle() != $langueTrad->getLanLibelle())) {
                //AJoute la traduction au dico de la langue
                $langueTrad->addTraduction($traduction);
                //Associe la traduction à l'objet
                $objetApidae->addTraduction($traduction);
                //Associe l'objet à la traduction :
                $traduction->setObjet($objetApidae);
                $this->em->merge($traduction);
            } else {
                $traduction = new TraductionObjetApidae();
                if($chaineLangue != "libelleFr" && !isset($data->nom->$chaineLangue)) {
                    $traduction->setTraNom($data->nom->libelleFr);
                } else {
                    $traduction->setTraNom($data->nom->$chaineLangue);
                }
                //Presentation
                if(isset($data->presentation)) {
                    $presentation = $data->presentation;
                    if(isset($presentation->descriptifCourt->$chaineLangue)) {
                        $traduction->setTraDescriptionCourte($presentation->descriptifCourt->$chaineLangue);
                    }else if(isset($presentation->descriptifCourt->libelleFr)) {
                        //Par défaut si n'existe pas dans la langue demandée
                        $traduction->setTraDescriptionCourte($presentation->descriptifCourt->libelleFr);
                    } else {
                        $traduction->setTraDescriptionCourte(null);
                    }
                    if(isset($presentation->descriptifDetaille->$chaineLangue)) {
                        $traduction->setTraDescriptionLongue($presentation->descriptifDetaille->$chaineLangue);
                    }else if(isset($presentation->descriptifDetaille->libelleFr)) {
                        $traduction->setTraDescriptionLongue($presentation->descriptifDetaille->libelleFr);
                    } else {
                        $traduction->setTraDescriptionLongue(null);
                    }
                }
                $traduction->setTraDescriptionPersonnalisee(null);
                $traduction->setTraBonsPlans(null);
                $traduction->setTraDateEnClair(null);
                $traduction->setTraTarifEnClair(null);
                $traduction->setTraInfosSup(null);

                //Associe la langue à la traduction
                $traduction->setLangue($langueTrad);
                //AJoute la traduction au dico de la langue
                $langueTrad->addTraduction($traduction);
                //Associe la traduction à l'objet
                $objetApidae->addTraduction($traduction);
                //Associe l'objet à la traduction :
                $traduction->setObjet($objetApidae);
                $this->em->persist($traduction);
            }
            $this->em->flush();

            //-------------------- Types de Public ----------------------
            if(isset($data->prestations->typesClientele)) {
                $tab = $data->prestations;
                for($i = 0; $i < count($tab->typesClientele); $i++) {
                    $typeClient = $this->em->getRepository(TypePublic::class)->findOneByTypId(($tab->typesClientele[$i]->id));
                    if($typeClient == null) {
                        $typeClient = new TypePublic();
                        $typeClient->setTypId($tab->typesClientele[$i]->id);
                        if(isset($tab->typesClientele[$i]->$chaineLangue)) {
                            $typeClient->setTypLibelle($tab->typesClientele[$i]->$chaineLangue);
                        } else {
                            foreach($this->fichierRef as $v) {
                                if($v->elementReferenceType == "TypeClientele"
                                    && $v->id == $tab->typesClientele[$i]->id) {
                                    if(isset($v->$chaineLangue)) {
                                        $typeClient->setTypLibelle($v->$chaineLangue);
                                    } else {
                                        $typeClient->setTypLibelle(null);
                                    }
                                }
                            }
                        }
                        if(isset($tab->typesClientele[$i]->familleCritere->$chaineLangue)) {
                            $typeClient->setFamilleCritere($tab->typesClientele[$i]->familleCritere->$chaineLangue);
                        } else {
                            foreach($this->fichierRef as $v) {
                                if($v->elementReferenceType == "FamilleCritere"
                                    && $v->id == $tab->typesClientele[$i]->id) {
                                    if(isset($v->$chaineLangue)) {
                                        print("FamilleCritere :: ".$v->$chaineLangue);
                                        $typeClient->setFamilleCritere($v->$chaineLangue);
                                    } else {
                                        $typeClient->setFamilleCritere(null);
                                    }
                                }
                            }
                        }
                        if(isset($tab->typesClientele[$i]->tailleGroupeMax)) {
                            $typeClient->setMax($tab->typesClientele[$i]->tailleGroupeMax);
                        } else {
                            $typeClient->setMax(null);
                        }
                        if(isset($tab->typesClientele[$i]->tailleGroupeMin)) {
                            $typeClient->setMin($tab->typesClientele[$i]->tailleGroupeMin);
                        } else {
                            $typeClient->setMin(null);
                        }
                        //Associe la traduction au type de public
                        $typeClient->setTraduction($traduction);
                        //Ajoute le type de client au dico de la traduction :
                        $traduction->addTypePublic($typeClient);
                        $this->em->persist($typeClient);
                        $this->em->flush();
                    } else if($this->em->getRepository(TypePublic::class)->findOneByTypId(($tab->typesClientele[$i]->id)) != $typeClient) {
                        //Associe la traduction au type de public
                        $typeClient->setTraduction($traduction);
                        //Ajoute le type de client au dico de la traduction :
                        $traduction->addTypePublic($typeClient);
                        $this->em->merge($typeClient);
                        $this->em->flush();
                    }
                }
            }

            //-------------------- Moyens de Communication ----------------------
            if(isset($data->informations->moyensCommunication)) {
                $tab = $data->informations;
                for($i = 0; $i < count($tab->moyensCommunication); $i++) {
                    $com = new MoyenCommunication();
                    if(isset($tab->moyensCommunication[$i]->type->$chaineLangue)) {
                        $com->setMoyComLibelle($tab->moyensCommunication[$i]->type->$chaineLangue);
                    } else {
                        foreach($this->fichierRef as $v) {
                            if($v->elementReferenceType == "MoyenCommunicationType" &&
                                $v->id == $tab->moyensCommunication[$i]->type->id) {
                                $com->setMoyComLibelle($v->$chaineLangue);
                            }
                        }
                    }
                    $com->setMoyComCoordonnees($tab->moyensCommunication[$i]->coordonnees->fr);
                    //associe la traduction à l'objet
                    $com->setTraduction($traduction);
                    //Ajoute le moyen de communication au dico de la traduction :
                    $traduction->addMoyenCommunication($com);
                    $this->em->persist($com);
                    $this->em->flush();
                }
            }
            //-------------------- Equipements ----------------------
            if(isset($data->prestations->conforts)) {
                $tab = $data->prestations;
                for($i = 0; $i < count($tab->conforts); $i++) {
                    $equipement = $this->em->getRepository(Equipement::class)->findOneByEquId(($tab->conforts[$i]->id));
                    if($equipement == null) {
                        $equipement = new Equipement();
                        $equipement->setEquId($tab->conforts[$i]->id);
                        if(isset($tab->conforts[$i]->$chaineLangue)) {
                            $equipement->setEquLibelle($tab->conforts[$i]->$chaineLangue);
                        } else if(isset($tab->conforts[$i]->libelleFr)) {
                            $equipement->setEquLibelle($tab->conforts[$i]->libelleFr);
                        } else {
                            $equipement->setEquLibelle(null);
                        }
                        if(isset($tab->conforts)) {
                            foreach($this->fichierRef as $v) {
                                if($v->elementReferenceType == "PrestationConfort"
                                    && isset($tab->conforts[$i]->id)
                                    && $v->id == $tab->conforts[$i]->id) {
                                    $equipement->setEquLibelle($v->$chaineLangue);
                                    $equipement->setEquType("Confort");
                                }
                            }
                        }
                        if(isset($tab->conforts[$i]->description)) {
                            $equipement->setEquInfosSup($tab->conforts[$i]->description);
                        } else {
                            $equipement->setEquInfosSup(null);
                        }
                        //Associe l'équipement à la traduction
                        $equipement->setTraduction($traduction);
                        //Ajoute l'équipement au dico de la traduction :
                        $traduction->addEquipement($equipement);
                        $this->em->persist($equipement);
                    } else if($this->em->getRepository(Equipement::class)->findOneByEquId(($tab->conforts[$i]->id)) != $equipement) {
                        //Associe l'équipement à la traduction
                        $equipement->setTraduction($traduction);
                        //Ajoute l'équipement au dico de la traduction :
                        $traduction->addEquipement($equipement);
                        $this->em->merge($equipement);
                    }
                }
            }
            if(isset($data->prestations->equipements)) {
                $tab = $data->prestations;
                for($i = 0; $i < count($tab->equipements); $i++) {
                    $equipement = $this->em->getRepository(Equipement::class)->findOneByEquId(($tab->equipements[$i]->id));
                    if($equipement == null) {
                        $equipement = new Equipement();
                        $equipement->setEquId($tab->equipements[$i]->id);
                        foreach($this->fichierRef as $v) {
                            if($v->elementReferenceType == "PrestationEquipement"
                                && isset($tab->equipements[$i]->id)
                                && $v->id == $tab->equipements[$i]->id) {
                                $equipement->setEquLibelle($v->$chaineLangue);
                                $equipement->setEquType("Equipement");
                            }
                        }
                        if(isset($tab->equipements[$i]->description)) {
                            $equipement->setEquInfosSup($tab->equipements[$i]->description);
                        } else {
                            $equipement->setEquInfosSup(null);
                        }
                        //Associe l'équipement à la traduction
                        $equipement->setTraduction($traduction);
                        //Ajoute l'équipement au dico de la traduction :
                        $traduction->addEquipement($equipement);
                        $this->em->persist($equipement);
                    } else if($this->em->getRepository(Equipement::class)->findOneByEquId(($tab->equipements[$i]->id)) != $equipement) {
                        //Associe l'équipement à la traduction
                        $equipement->setTraduction($traduction);
                        //Ajoute l'équipement au dico de la traduction :
                        $traduction->addEquipement($equipement);
                        $this->em->merge($equipement);
                    }
                }
            }

            //-------------------- Services ----------------------
            //services
            if(isset($data->prestations->services)) {
                $tab = $data->prestations;
                for($i = 0; $i < count($tab->services); $i++) {
                    $service = $this->em->getRepository(Service::class)->findOneBySerId($tab->services[$i]->id);
                    if($service == null) {
                        if(isset($tab->services)) {
                            $service = new Service();
                            $service->setSerId($tab->services[$i]->id);
                            $this->traitementServices($tab, $i, $service, $chaineLangue);
                            $service->setSerType($tab->services[$i]->elementReferenceType);
                        }
                        //Associe le service à la traduction :
                        $service->addTraduction($traduction);
                        //Ajoute le service au dico de la traduction :
                        $traduction->addService($service);
                        $this->em->persist($service);
                    } else if($this->em->getRepository(Service::class)->findOneBySerId($tab->services[$i]->id) != $service){
                        //Associe le service à la traduction :
                        $service->addTraduction($traduction);
                        //Ajoute le service au dico de la traduction :
                        $traduction->addService($service);
                        $this->em->merge($service);
                    }
                }
            }

            //modes de paiement :
            if(isset($data->descriptionTarif->modesPaiement)) {
                $tab = $data->descriptionTarif;
                for($i = 0; $i < count($tab->modesPaiement); $i++) {
                    $service = $this->em->getRepository(Service::class)->findOneBySerId($tab->modesPaiement[$i]->id);
                    if($service == null) {
                        if(isset($tab->modesPaiement)) {
                            $service = new Service();
                            $service->setSerId($tab->modesPaiement[$i]->id);
                            $this->traitementServices($tab, $i, $service, $chaineLangue);
                            $service->setSerType($tab->modesPaiement[$i]->elementReferenceType);
                        }
                        //Associe le service à la traduction :
                        $service->addTraduction($traduction);
                        //Ajoute le service au dico de la traduction :
                        $traduction->addService($service);
                        $this->em->persist($service);
                    } else if($this->em->getRepository(Service::class)->findOneBySerId(($tab->modesPaiement[$i]->id)) != $service){
                        //Associe le service à la traduction :
                        $service->addTraduction($traduction);
                        //Ajoute le service au dico de la traduction :
                        $traduction->addService($service);
                        $this->em->merge($service);
                    }
                }
            }

            //Handicap (tourismesAdaptes)
            if(isset($data->prestations->tourismesAdaptes)) {
                $tab = $data->prestations;
                for($i = 0; $i < count($tab->tourismesAdaptes); $i++) {
                    $service = $this->em->getRepository(Service::class)->findOneBySerId($tab->tourismesAdaptes[$i]->id);
                    if($service == null) {
                        if(isset($tab->tourismesAdaptes)) {
                            $service = new Service();
                            $service->setSerId($tab->tourismesAdaptes[$i]->id);
                            $this->traitementServices($tab, $i, $service, $chaineLangue);
                            $service->setSerType($tab->tourismesAdaptes[$i]->elementReferenceType);
                        }
                        //Associe le service à la traduction :
                        $service->addTraduction($traduction);
                        //Ajoute le service au dico de la traduction :
                        $traduction->addService($service);
                        $this->em->persist($service);
                    } else if($this->em->getRepository(Service::class)->findOneBySerId($tab->tourismesAdaptes[$i]->id) != $service){
                        //Associe le service à la traduction :
                        $service->addTraduction($traduction);
                        //Ajoute le service au dico de la traduction :
                        $traduction->addService($service);
                        $this->em->merge($service);
                    }
                }
            }

            //TODO ?Langues parlées
            //-------------------- Labels ----------------------
            //labelsQualité
            if(isset($data->$chaineInformations->labels)) {
                foreach($data->$chaineInformations->labels as $v) {
                    $label = $this->em->getRepository(LabelQualite::class)->findOneByLabId($v->id);
                    if($label != null) {
                        $objetApidae->addLabelQualite($label);
                        $label->addObjetApidae($objetApidae);
                        $this->em->merge($objetApidae);
                        $this->em->merge($label);
                    } else {
                        $value = $this->traitementReference($v->elementReferenceType, $v->id, $this->fichierRef);
                        if($value != false) {
                            $classement = $this->traitementReference($value->elementReferenceType,$value->typeLabel->id);
                            //pour chaque langue :
                            $labLibelle= "";
                            $labClassement = "";
                            foreach($languesSite as $key => $val) {
                                $shortCut = $val[0] . $val[1];
                                $langue = "libelle" . $shortCut;
                                $lib = $v->langue;
                                $labLibelle .= '@'.$shortCut.':'.$lib;
                                $labClassement .= '@'.$shortCut.':'.$lib;
                            }
                            $label->setLabLibelle($labLibelle);
                            $label->setLabClassement($labClassement);

                            $objetApidae->addLabelQualite($label);
                            $label->addObjetApidae($objetApidae);
                            $this->em->persist($objetApidae);
                            $this->em->persist($label);
                        }
                    }
                    //Ajoute la traduction au dico du label
                    $label->addObjetApidae($objetApidae);
                    //Ajoute le label au dico de la traduction
                    $objetApidae->addLabelQualite($label);
                    $this->traitementLabelsQualite($label);
                }
            }
            //étoiles
            if(isset($data->$chaineInformations->classement)) {
                if(isset($data->$chaineInformations->classement)) {
                    foreach($this->fichierRef as $v) {
                        if($v->elementReferenceType == $typeObj."Classement" &&
                            $v->id == $data->$chaineInformations->classement->id) {
                            $objetApidae->setObjEtoile($v->$chaineLangue);
                            break;
                        }
                    }
                }
            }

            //-------------------- Tarifs ----------------------
            if(isset($data->descriptionTarif)) {
                $tab = $data->descriptionTarif;
                if(isset($tab->tarifsEnClair)) {
                    if(isset($tab->tarifsEnClair->$chaineLangue)) {
                        $traduction->setTraTarifEnClair($tab->tarifsEnClair->$chaineLangue);
                    } else if(isset($tab->tarifsEnClair->libelleFr)){
                        $traduction->setTraTarifEnClair($tab->tarifsEnClair->libelleFr);
                    } else {
                        $traduction->setTraTarifEnClair(null);
                    }
                }
                if(isset($tab->periodes[0]->tarifs)) {
                    $tarifs = $tab->periodes[0];
                    for($i = 0; $i < count($tab->periodes[0]->tarifs); $i++) {
                        $tarif = new Tarif();
                        $tarif->setTarDevise($tarifs->tarifs[$i]->devise);
                        if(isset($tarifs->tarifs[$i]->maximum)) {
                            $tarif->setTarMax($tarifs->tarifs[$i]->maximum);
                        } else {
                            $tarif->setTarMax(null);
                        }
                        if(isset($tarifs->tarifs[$i]->minimum)) {
                            $tarif->setTarMin($tarifs->tarifs[$i]->minimum);
                        } else {
                            $tarif->setTarMin(null);
                        }
                        if(isset($tarifs->tarifs[$i]->type->$chaineLangue)) {
                            $tarif->setTarLibelle($tarifs->tarifs[$i]->type->$chaineLangue);
                        } else {
                            foreach($this->fichierRef as $v) {
                                if($v->elementReferenceType == "TarifType" &&
                                    $v->id == $tarifs->tarifs[$i]->type->id) {
                                    $tarif->setTarLibelle($v->$chaineLangue);
                                }
                            }
                        }
                        if(isset($tab->indicationTarif)) {
                            $tarif->setTarIndication($tab->indicationTarif);
                        } else {
                            $tarif->setTarIndication(null);
                        }

                        //Associe le tarif à la traduction :
                        $tarif->setTraduction($traduction);
                        //Ajoute le tarif à la traduction :
                        $traduction->addTarif($tarif);
                        $this->em->persist($tarif);
                    }
                }
            }

            //-------------------- Ouvertures ----------------------
            if(isset($data->ouverture)) {
                if(isset($data->ouverture->periodeEnClair)) {
                    if(isset($data->ouverture->periodeEnClair->$chaineLangue)) {
                        $traduction->setTraDateEnClair($data->ouverture->periodeEnClair->$chaineLangue);
                    } else if(isset($data->ouverture->periodeEnClair->libelleFr)){
                        $traduction->setTraDateEnClair($data->ouverture->periodeEnClair->libelleFr);
                    } else {
                        $traduction->setTraDateEnClair(null);
                    }
                }
                if(isset($data->ouverture->periodesOuvertures)) {
                    $tab = $data->ouverture;
                    for($i = 0; $i < count($tab->periodesOuvertures); $i++) {
                        $ouverture = new Ouverture();
                        $ouverture->setOuvDateDebut($tab->periodesOuvertures[$i]->dateDebut);
                        $ouverture->setOuvDateFin($tab->periodesOuvertures[$i]->dateFin);
                        if(isset($tab->periodesOuvertures[$i]->complementHoraire)) {
                            if(isset($tab->periodesOuvertures[$i]->complementHoraire->$chaineLangue)) {
                                $ouverture->setSerInfosSup($tab->periodesOuvertures[$i]->complementHoraire->$chaineLangue);
                            } else if(isset($tab->periodesOuvertures[$i]->complementHoraire->libelleFR)) {
                                $ouverture->setSerInfosSup($tab->periodesOuvertures[$i]->complementHoraire->libelleFr);
                            } else {
                                $ouverture->setSerInfosSup(null);
                            }
                        }
                        //Associe l'ouverture à la traduction :
                        $ouverture->setTraduction($traduction);
                        //Ajoute l'ouverture au dico de la traduction :
                        $traduction->addOuverture($ouverture);
                        $this->em->persist($ouverture);
                    }
                }
            }

            //-------------------- Multimedias ----------------------
            if(isset($data->illustrations)) {
                for($i = 0; $i < count($data->illustrations); $i++) {
                    $multi = new Multimedia();
                    if(isset($data->illustrations[$i]->nom->$chaineLangue)) {
                        $multi->setMulLibelle($data->illustrations[$i]->nom->$chaineLangue);
                    } else {
                        $multi->setMulLibelle(null);
                    }
                    $multi->setMulLocked($data->illustrations[$i]->locked);
                    $multi->setMulType($data->illustrations[$i]->type);
                    if(isset($data->illustrations[$i]->traductionFichiers[0]->urlListe)) {
                        $multi->setMulUrlListe($data->illustrations[$i]->traductionFichiers[0]->urlListe);
                    } else {
                        $multi->setMulUrlListe(null);
                    }
                    if(isset($data->illustrations[$i]->traductionFichiers[0]->urlFiche)) {
                        $multi->setMulUrlFiche($data->illustrations[$i]->traductionFichiers[0]->urlFiche);
                    } else {
                        $multi->setMulUrlFiche(null);
                    }
                    if(isset($data->illustrations[$i]->traductionFichiers[0]->urlDiaporama)) {
                        $multi->setMulUrlDiapo($data->illustrations[$i]->traductionFichiers[0]->urlDiaporama);
                    } else {
                        $multi->setMulUrlDiapo(null);
                    }

                    //Associe le media à la traduction :
                    $multi->setTraduction($traduction);
                    //Ajoute le média au dico de la traduction :
                    $traduction->addMultimedia($multi);
                    //print($multi->getMulLibelle()."</br>");
                    $this->em->persist($multi);
                }
            }

            //-------------------- Capacite ----------------------
            //TODO capacite



            //-------------------- Duree ----------------------
            //TODO duree


            $i++;
        }

        //-------------------- ObjetsLies ----------------------
        //TODO objetsLies
        if(isset($data->liens)) {
            for ($i = 0; $i < count($data->liens); $i++) {
                if(isset($data->liens->liensObjetsTouristiquesTypes[$i]->objetTouristique->id)) {
                    $objetLie = new ObjetLie();
                    $objetLie->setObjet($objetApidae);
                    $objetLie->setIdObjetLie($data->liens->liensObjetsTouristiquesTypes[$i]->objetTouristique->id);
                    $objetApidae->addObjetLie($objetLie);
                }
            }
        }
    }

    private function traitementReference($type, $id) {
        foreach($this->fichierRef as $v) {
            if ($v->elementReferenceType == $type
                && $v->id == $id) {
                return $v;
            }
        }
        return false;
    }

    private function traitementFamilleCritere($id, $chaineLangue) {
        $v = $this->traitementReference("FamilleCritere", $id);
        if($v != false) {
            if(isset($v->$chaineLangue)) {
                return $v->$chaineLangue;
            } else if (isset($v->libelleFr)) {
                return $v->libelleFr;
            } else {
                return "Pas de libelle disponible";
            }
        }
    }

    private function traitementServices($tab, $i, $service, $chaineLangue) {
        if(isset($tab->services[$i]->id)) {
            $v = $this->traitementReference("PrestationService", $tab->services[$i]->id);
            if($v != false) {
                $this->traitementServiceLibelle($v, $service, $chaineLangue);
                $this->traitementServiceDetails($service, $v, $chaineLangue);
            }
        }

        if(isset($tab->modesPaiement[$i]->id)) {
            $v = $this->traitementReference("ModePaiement", $tab->modesPaiement[$i]->id);
            if($v != false) {
                $this->traitementServiceLibelle($v, $service, $chaineLangue);
                $this->traitementServiceDetails($service, $v, $chaineLangue);
            }
        }

        if(isset($tab->tourismesAdaptes[$i]->id)) {
            print("tourisme");
            $v = $this->traitementReference("TourismeAdapte", $tab->tourismesAdaptes[$i]->id);
            if($v != false) {
                print("yep");
                $this->traitementServiceLibelle($v, $service, $chaineLangue);
                $this->traitementServiceDetails($service, $v, $chaineLangue);
            }
        }
    }

    private function traitementServiceLibelle($v, $service, $chaineLangue) {
        if(isset($v->$chaineLangue)) {
            $service->setSerLibelle($v->$chaineLangue);
        } else if(isset($v->libelleFr)) {
            $service->setSerLibelle($v->libelleFr);
        }
    }

    private function traitementServiceDetails($service, $v, $chaineLangue) {
        if(isset($v->familleCritere)) {
            $type = $this->traitementFamilleCritere($v->familleCritere->id, $chaineLangue);
            $service->setSerFamilleCritere($type);
        } else {
            $service->setSerFamilleCritere(null);
        }
        if(isset($v->description)) {
            $service->setSerInfosSup($v->description);
        } else {
            $service->setSerInfosSup(null);
        }
    }

    private function traitementLabelsQualite($label) {
        if($this->em->getRepository(LabelQualite::class)->findOneByLabLibelle($label) == null) {
            $this->em->persist($label);
        }
    }

    private function traitementCategorie($cat, $objetApidae) {
        //On vérifie si la catégorie existe déjà
        $catExist = $this->em->getRepository(Categorie::class)->findOneByCatLibelle($cat);
        if($catExist == null) {
            $categorie = new Categorie();
            $categorie->setCatLibelle($cat);
            //Associe la catégorie à l'objet :
            $objetApidae->addCategorie($categorie);
            //Ajout de lobjet à la catégorie :
            $categorie->addObjet($objetApidae);
            $this->em->persist($categorie);
        } else if($this->em->getRepository(ObjetApidae::class)->findOneByIdObj($objetApidae->getIdObjet()) != $objetApidae){
            //Associe la catégorie à l'objet
            $objetApidae->addCategorie($catExist);
            $catExist->addObjet($objetApidae);
            $this->em->merge($catExist);
        }
    }

    private function getLibelleLang($str, $locale = '') {
        if (empty ($locale)) {
            $locale = SIT_LANGUE;
        }
        $debut = strpos($str, '@' . $locale . ':');
        if ($debut === false) {
            return $str;
        }
        $debut += strlen('@' . $locale . ':');
        $fin = strpos($str, '@', $debut);
        return substr($str, $debut, $fin - $debut);
    }
}
