<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\Activite;
use ApidaeBundle\Entity\ActiviteType;
use ApidaeBundle\Entity\Duree;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Hebergement;
use ApidaeBundle\Entity\InformationsTarif;
use ApidaeBundle\Entity\ObjetLie;
use ApidaeBundle\Entity\Restaurant;
use ApidaeBundle\Entity\TarifType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ApidaeBundle\Entity\SelectionApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Entity\LabelQualite;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Commune;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\Equipement;
use ApidaeBundle\Entity\Service;
use ApidaeBundle\Entity\MoyenCommunication;
use ApidaeBundle\Entity\Multimedia;
use ApidaeBundle\Entity\Ouverture;
use ApidaeBundle\Entity\TypePublic;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="traitement")
 */
class Traitement extends ContainerAwareCommand {
    private $em;
    private $communes;
    private $fichierRef;
    private $total;
    private $sansCategorie;
    private $sansType;

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


        $this->total = 0;
        $this->sansCategorie = 0;
        $this->sansType = 0;

        //Récupération fichiers :
        try {
            $export = file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/selections.json");
            $this->communes = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/communes.json"));
            $this->fichierRef = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/elements_reference.json"));
            $selections_data = json_decode($export);
            foreach ($selections_data as $value) {
                $selectionApidae = $this->em->getRepository(SelectionApidae::class)->findOneByIdSelectionApidae($value->id);
                if ($selectionApidae == null) {
                    $selectionApidae = new SelectionApidae();
                    $selectionApidae->setIdSelectionApidae($value->id);
                    $selectionApidae->setSelLibelle($value->nom);
                    $this->em->persist($selectionApidae);
                    $this->em->flush();
                } else {
                    $selectionApidae->setSelLibelle($value->nom);
                    $this->em->merge($selectionApidae);
                }
                print($selectionApidae->getSelLibelle() . "\n");
                foreach ($value->objetsTouristiques as $val) {
                    print($val->id . "\n");
                    //=> $data = aller chercher le bon fichier dans objetsModifies
                    $data = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/objets_modifies/objets_modifies-" . $val->id . ".json"));
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
                    $tab[0] = strtolower($tab[0]);
                    $chaineType = implode($tab) . "Type";
                    if ($data->type == "FETE_ET_MANIFESTATION") {
                        $chaineType = "typesManifestation";
                    }
                    $this->traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite);
                }
            }
            //---
            $output->writeln("Total objet = ".$this->total." \n Sans categorie = ".$this->sansCategorie." \n Sans type = ".$this->sansType);
            $output->writeln("Fin de traitement.");
        } catch(Exception $e) {
            $output->writeln("Problème : ".$e->getMessage());
        }
    }

    private function traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite) {
        $this->total++;
        //-------------------- ObjetApidae ----------------------
        $update = true;
        if($selectionApidae->getSelLibelle() == "Restaurants") {
            $objetApidae = $this->em->getRepository(Restaurant::class)->findOneByIdObj($data->id);
            if($objetApidae == null) {
                $update = false;
                $objetApidae = new Restaurant();
            }
        } else if($selectionApidae->getSelLibelle() == "Hébergements") {
            $objetApidae = $this->em->getRepository(Hebergement::class)->findOneByIdObj($data->id);
            if($objetApidae == null) {
                $update = false;
                $objetApidae = new Hebergement();
            }
        } else if($selectionApidae->getSelLibelle() == "Activités") {
            $objetApidae = $this->em->getRepository(Activite::class)->findOneByIdObj($data->id);
            if($objetApidae == null) {
                $update = false;
                $objetApidae = new Activite();
            }
        } else if($selectionApidae->getSelLibelle() == "Evénements") {
            $objetApidae = $this->em->getRepository(Evenement::class)->findOneByIdObj($data->id);
            if($objetApidae == null) {
                $update = false;
                $objetApidae = new Evenement();
            }
        }
        if(!$update) {
            $this->updateObjetApidae($objetApidae, $data, $selectionApidae, $languesSite, false);
        } else {
            //update de l'objet
            $this->updateObjetApidae($objetApidae, $data, $selectionApidae, $languesSite, true);
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
                    $this->em->merge($objetApidae);
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

        //-------------------- Categories ----------------------
        //Récupération de la/des catégorie(s)
        /*if(!isset($data->$chaineInformations->categories)) {
            $this->sansCategorie++;
            print("Pas de categorie \n");
        }
        if(!isset($data->$chaineInformations->typesManifestation) &&
            !isset($data->$chaineInformations->typesHabitation) &&
            !isset($data->$chaineInformations->$chaineType)) {
            $this->sansType++;
            print("Pas de type \n");
        }*/

        if(isset($data->$chaineInformations->categories)) {
            foreach($data->$chaineInformations->categories as $categorie) {
                $v = $this->traitementReference($categorie->elementReferenceType,$categorie->id);
                $lanLib =$this->traitementLibelleLangues($languesSite, $v);
                $this->traitementCategorieDetails($lanLib, $categorie, $objetApidae);
                if(isset($v->familleCritere)) {
                    if(!$this->em->getRepository(Categorie::class)->findOneByCatId(($v->familleCritere->id))) {
                        $val = $this->traitementReference($v->familleCritere->elementReferenceType, $v->familleCritere->id);
                        $lanLib =$this->traitementLibelleLangues($languesSite, $val);
                        $this->traitementCategorieDetails($lanLib, $v->familleCritere, $objetApidae);
                    }
                }
            }
        } else if(isset($data->$chaineInformations->typesManifestation)) {
            $this->traitementTypeCategories($data->$chaineInformations->typesManifestation, $objetApidae, $languesSite);
        }
        if(isset($data->$chaineInformations->specialites)) {
            $this->traitementTypeCategories($data->$chaineInformations->specialites, $objetApidae, $languesSite);
        } else if(isset($data->$chaineInformations->typesHabitation)) {
            $this->traitementTypeCategories($data->$chaineInformations->typesHabitation, $objetApidae, $languesSite);
        }
        if(isset($data->$chaineInformations->$chaineType)) {
            //$this->traitementTypeCategories($data->$chaineInformations->$chaineType, $objetApidae, $languesSite);
            $tab = $data->$chaineInformations->$chaineType;
            if($chaineType == "typesManifestation") {
                foreach($tab as $value) {
                    $v = $this->traitementReference($value->elementReferenceType, $value->id);
                    $lanLib = $this->traitementLibelleLangues($languesSite, $v);
                    $this->traitementCategorieDetails($lanLib, $value, $objetApidae);
                }
            } else {
                $v = $this->traitementReference($tab->elementReferenceType, $tab->id);
                $lanLib = $this->traitementLibelleLangues($languesSite, $v);
                $this->traitementCategorieDetails($lanLib, $tab, $objetApidae);

            }
        }
        if(isset($data->$chaineInformations->activites)) {
            $this->traitementTypeCategories($data->$chaineInformations->activites, $objetApidae, $languesSite);
        }
        if(isset($data->$chaineInformations->themes)) {
            $this->traitementTypeCategories($data->$chaineInformations->themes, $objetApidae, $languesSite);
        }
/*
        if(isset($data->$chaineInformations->activitesSportives)) {
            $this->traitementTypeCategories($data->$chaineInformations->activitesSportives, $objetApidae, $languesSite);
        }
        //TEST prestationActivite
        if(isset($data->prestations->activites)) {
            $this->traitementTypeCategories($data->prestations->activites, $objetApidae, $languesSite);
        }*/

        //--------------------Langue ----------------------
        $i = 0;
        $langueTrad= "";
        foreach($languesSite as $key => $value) {
            $shortCut = $value[0] . $value[1];
            $langue = $this->em->getRepository(Langue::class)->findOneByLanLibelle($value);
            if ($langue == null) {
                $langue = new Langue();
                $langue->setCodeLangue($i);
                $langue->setLanLibelle($value);
                $langue->setLanShortCut($shortCut);
                $langue->setLanIso("?");
                $this->em->persist($langue);
                $langueTrad = $langue;
            } else {
                $langueTrad = $langue;
            }
            $chaineLangue = "libelle" . $shortCut;

            //------------------------------------------------ Traduction -------------------------------------------------
            //$traduction = new TraductionObjetApidae();
            $traduction = $this->em->getRepository(TraductionObjetApidae::class)->findOneBy(array("langue"=> $langue, "objet"=>$objetApidae));
            if($traduction != null) {
                $this->updateTraduction($traduction, $data, $chaineLangue, $langueTrad, $objetApidae, true);
            } else {
                $traduction = new TraductionObjetApidae();
                $this->updateTraduction($traduction, $data, $chaineLangue, $langueTrad, $objetApidae, false);
            }
            $i++;
        }


        //--- obj changements
        $nom = $this->traitementLibelleLangues($languesSite, $data->nom);
        $objetApidae->setNom($nom);
        $objetApidae->setDateEnClair(null);
        $objetApidae->setTarifEnClair(null);

        //-------------------- Types de Public ----------------------
        if(isset($data->prestations->typesClientele)) {
            $tab = $data->prestations;
            for($i = 0; $i < count($tab->typesClientele); $i++) {
                $typeClient = $this->em->getRepository(TypePublic::class)->findOneByTypId($tab->typesClientele[$i]->id);
                if($typeClient == null) {
                    $typeClient = new TypePublic();
                    $typeClient->setTypId($tab->typesClientele[$i]->id);
                    $this->updateTypeCLient($typeClient, $i, $tab, $languesSite, $objetApidae, false);
                } else {
                    $this->updateTypeCLient($typeClient, $i, $tab, $languesSite, $objetApidae, true);
                }
            }
        }

        //-------------------- Moyens de Communication ----------------------
        if(isset($data->informations->moyensCommunication)) {
            $tab = $data->informations;
            for($i = 0; $i < count($tab->moyensCommunication); $i++) {
                $com = $this->em->getRepository(MoyenCommunication::class)->findOneByIdMoyCom($tab->moyensCommunication[$i]->identifiant);
                $update = true;
                if($com == null) {
                    $update = false;
                    $com = new MoyenCommunication();
                }
                if(isset($tab->moyensCommunication[$i])) {
                    $v = $this->traitementReference($tab->moyensCommunication[$i]->type->elementReferenceType, $tab->moyensCommunication[$i]->type->id);
                    $lib = $this->traitementLibelleLangues($languesSite, $v);
                    $com->setMoyComLibelle($lib);
                    $com->setIdMoyCom($tab->moyensCommunication[$i]->identifiant);
                }
                $com->setMoyComCoordonnees($tab->moyensCommunication[$i]->coordonnees->fr);
                //associe la traduction à l'objet
                $com->setObjetApidae($objetApidae);
                if(!$objetApidae->getMoyensCommunications()->contains($com)) {
                    $objetApidae->addMoyenCommunication($com);
                }
                if($update) {
                    $this->em->merge($com);
                } else {
                    $this->em->persist($com);
                    $this->em->flush();
                }
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
                    if(isset($tab->conforts[$i])) {
                        $v = $this->traitementReference($tab->conforts[$i]->elementReferenceType, $tab->conforts[$i]->id);
                        $lib = $this->traitementLibelleLangues($languesSite, $v);
                        $equipement->setEquLibelle($lib);
                        $equipement->setEquType("Confort");
                    }
                    if(isset($tab->conforts[$i]->description)) {
                        $equipement->setEquInfosSup($tab->conforts[$i]->description);
                    } else {
                        $equipement->setEquInfosSup(null);
                    }
                    //Associe l'équipement à la traduction
                    $equipement->addObjetApidae($objetApidae);
                    //Ajoute l'équipement au dico de la traduction :
                    $objetApidae->addEquipement($equipement);
                    $this->em->persist($equipement);
                } else if($this->em->getRepository(Equipement::class)->findOneByEquId(($tab->conforts[$i]->id)) != $equipement) {
                    //Associe l'équipement à la traduction
                    $equipement->setObjetApidae($objetApidae);
                    //Ajoute l'équipement au dico de la traduction :
                    $objetApidae->addEquipement($equipement);
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
                }
                if(isset($tab->equipements[$i]->id)) {
                    $v = $this->traitementReference($tab->equipements[$i]->elementReferenceType, $tab->equipements[$i]->id);
                    if($v != false) {
                        $lib = $this->traitementLibelleLangues($languesSite, $v);
                        $equipement->setEquLibelle($lib);
                        $equipement->setEquType("Equipement");
                    }
                }
                if(isset($tab->equipements[$i]->description)) {
                    $equipement->setEquInfosSup($tab->equipements[$i]->description);
                } else {
                    $equipement->setEquInfosSup(null);
                }
                if($this->em->getRepository(Equipement::class)->findOneByEquId(($tab->equipements[$i]->id)) != null) {
                    $this->updateEquipement($equipement, $objetApidae, true);
                } else {
                    $this->updateEquipement($equipement, $objetApidae, false);
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
                    if (isset($tab->services)) {
                        $service = new Service();
                    }
                }
                $service->setSerId($tab->services[$i]->id);
                $this->traitementServices($tab, $i, $service, $languesSite);
                $service->setSerType($tab->services[$i]->elementReferenceType);
                if($this->em->getRepository(Service::class)->findOneBySerId($tab->services[$i]->id) != null){
                    $this->updateService($service, $objetApidae, true);
                } else {
                    $this->updateService($service, $objetApidae, false);
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
                    }
                }
                $service->setSerId($tab->modesPaiement[$i]->id);
                $this->traitementServices($tab, $i, $service, $languesSite);
                $service->setSerType($tab->modesPaiement[$i]->elementReferenceType);
                if($this->em->getRepository(Service::class)->findOneBySerId(($tab->modesPaiement[$i]->id)) != null){
                    $this->updateService($service, $objetApidae, true);
                } else {
                    $this->updateService($service, $objetApidae, false);
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
                    }
                }
                $service->setSerId($tab->tourismesAdaptes[$i]->id);
                $this->traitementServices($tab, $i, $service, $languesSite);
                $service->setSerType($tab->tourismesAdaptes[$i]->elementReferenceType);
                if($this->em->getRepository(Service::class)->findOneBySerId($tab->tourismesAdaptes[$i]->id) != null){
                    $this->updateService($service, $objetApidae, true);
                } else {
                    $this->updateService($service, $objetApidae, false);
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
                    if(!$objetApidae->getLabelsQualite()->contains($label)) {
                        $objetApidae->addLabelQualite($label);
                        $label->addObjetApidae($objetApidae);
                        $this->em->merge($objetApidae);
                        $this->em->merge($label);
                    }
                } else {
                    $label = new LabelQualite();
                    $classementLabel = $this->traitementReference($v->elementReferenceType, $v->id, $this->fichierRef);
                    if($classementLabel != false) {
                        $typeLabel = $this->traitementReference($classementLabel->typeLabel->elementReferenceType, $classementLabel->typeLabel->id);
                        $labClassement = $this->traitementLibelleLangues($languesSite, $classementLabel);
                        $labLibelle = $this->traitementLibelleLangues($languesSite, $typeLabel);
                        $label->setLabId($classementLabel->id);
                        $label->setLabLibelle($labLibelle);
                        $label->setLabClassement($labClassement);

                        $objetApidae->addLabelQualite($label);
                        $label->addObjetApidae($objetApidae);
                        $this->em->persist($objetApidae);
                        $this->em->persist($label);
                        $this->em->flush();
                    }
                }
            }
        }
        //étoiles
        if(isset($data->$chaineInformations->classement)) {
            if(isset($data->$chaineInformations->classement)) {
                $v = $this->traitementReference($data->$chaineInformations->classement->elementReferenceType, $data->$chaineInformations->classement->id);
                if($v != false) {
                    $lib = $this->traitementLibelleLangues($languesSite, $v);
                    $objetApidae->setObjEtoile($lib);
                }
            }
        }

        //-------------------- Tarifs ----------------------
        if(isset($data->descriptionTarif)) {
            $tab = $data->descriptionTarif;
            if(isset($data->descriptionTarif)) {
                $tab = $data->descriptionTarif;
                if (isset($tab->tarifsEnClair)) {
                    if (isset($tab->tarifsEnClair)) {
                        $lib = $this->traitementLibelleLangues($languesSite, $tab->tarifsEnClair);
                        $objetApidae->setTarifEnClair($lib);
                    }
                }
            }
            if(isset($tab->periodes[0]->tarifs)) {
                $tarifs = $tab->periodes[0];
                for($i = 0; $i < count($tab->periodes[0]->tarifs); $i++) {
                    $tarifType = $this->em->getRepository(TarifType::class)->findOneByIdTarif($tarifs->tarifs[$i]->type->id);
                    $update = true;
                    if($tarifType == null) {
                        $update = false;
                        $tarifType = new TarifType();
                        $v = $this->traitementReference($tarifs->tarifs[$i]->type->elementReferenceType, $tarifs->tarifs[$i]->type->id);
                        if($v != false) {
                            $tarifType->setIdTarif($v->id);
                            $tarifType->setTarLibelle($this->traitementLibelleLangues($languesSite, $v));
                            $tarifType->setOrdre($v->ordre);
                        }
                    }
                    $this->traitementInfosTarif($tarifType,$tarifs->tarifs[$i], $tab, $objetApidae, $update);
                }
            }
        }

        //-------------------- Ouvertures ----------------------
        if(isset($data->ouverture)) {
            if(isset($data->ouverture->periodeEnClair)) {
                if(isset($data->ouverture->periodeEnClair)) {
                    $objetApidae->setDateEnClair($this->traitementLibelleLangues($languesSite, $data->ouverture->periodeEnClair));
                }
            }
            if(isset($data->ouverture->periodesOuvertures)) {
                $tab = $data->ouverture;
                for($i = 0; $i < count($tab->periodesOuvertures); $i++) {
                    $ouverture = $this->em->getRepository(Ouverture::class)->findOneByIdOuverture($tab->periodesOuvertures[$i]->identifiant);
                    $update = true;
                    if($ouverture == null) {
                        $update = false;
                        $ouverture = new Ouverture();
                    }
                    $ouverture->setIdOuverture($tab->periodesOuvertures[$i]->identifiant);
                    $ouverture->setOuvDateDebut($tab->periodesOuvertures[$i]->dateDebut);
                    $ouverture->setOuvDateFin($tab->periodesOuvertures[$i]->dateFin);
                    if(isset($tab->periodesOuvertures[$i]->complementHoraire)) {
                        $ouverture->setOuvInfosSup($this->traitementLibelleLangues($languesSite, $tab->periodesOuvertures[$i]->complementHoraire));
                    }
                    //Associe l'ouverture à la traduction :
                    $ouverture->setObjetApidae($objetApidae);
                    if(!$objetApidae->getOuvertures()->contains($ouverture)) {
                        //Ajoute l'ouverture au dico de la traduction :
                        $objetApidae->addOuverture($ouverture);
                    }
                    if($update) {
                        $this->em->merge($ouverture);
                    } else {
                        $this->em->persist($ouverture);
                    }
                }
            }
        }

        //-------------------- Multimedias ----------------------
        if(isset($data->illustrations)) {
            for($i = 0; $i < count($data->illustrations); $i++) {
                $multimedia = $this->em->getRepository(Multimedia::class)->findOneByIdMultimedia($data->illustrations[$i]->identifiant);
                if($multimedia != null) {
                    $this->updateMultimedia($multimedia, $languesSite, $i, $data, $objetApidae, true);
                } else {
                    $multimedia = new Multimedia();
                    $this->updateMultimedia($multimedia,  $languesSite, $i, $data, $objetApidae, false);
                }
            }
        }

        //-------------------- Capacite ----------------------
        if(isset($data->$chaineInformations->capacite)) {
            $objetApidae->setCapacite($data->$chaineInformations->capacite);
        }

        //-------------------- Duree ----------------------
        //TODO duree
        if(isset($data->$chaineInformations->durees)) {
            $tab = array();
            if(isset($data->$chaineInformations->dureeSeance)) {
                $tab['dureeSeance'] = $data->$chaineInformations->dureeSeance;
            }
            if(isset($data->$chaineInformations->durees->nombreJours)) {
                $tab['nbJours'] = $data->$chaineInformations->nombreJours;
            }

            $objetApidae->setCapacite($tab);
            for($i = 0; $i < count($data->$chaineInformations->durees); $i++) {
                $v = $this->traitementReference($data->$chaineInformations->durees[$i]->elementReferenceType,
                    $data->$chaineInformations->durees[$i]->id);
                if($v != null) {
                    $duree = $this->em->getRepository(Duree::class)->findOneByIdDuree($v->id);
                    $update = true;
                    if($duree == null) {
                        $update = false;
                        $duree = new Duree();
                    }
                    $duree->setIdDuree($v->id);
                    $duree->setLibelle($this->traitementLibelleLangues($languesSite, $v));
                    $duree->setOrdre($v->ordre);
                    if(!$duree->getActivites()->contains($objetApidae)) {
                        $duree->addActivite($objetApidae);
                    }
                    if(!$objetApidae->getDurees()->contains($duree)) {
                        $objetApidae->addDuree($duree);
                    }
                    if($update) {
                        $this->em->merge($objetApidae);
                        $this->em->merge($duree);
                    } else {
                        $this->em->persist($duree);
                    }
                }
            }
        }

        //--------------- ActiviteType / RubriqueEquipement / patrimoineCulturelType --------------------
        if(isset($data->$chaineInformations->activiteType)) {
            $this->traitementActiviteTypes($data->$chaineInformations->activiteType, $languesSite, $objetApidae);
        } else if(isset($data->$chaineInformations->rubrique)) {
            $this->traitementActiviteTypes($data->$chaineInformations->rubrique, $languesSite, $objetApidae);
        } /*else if(isset($data->$chaineInformations->patrimoineCulturelType)) {
            $this->traitementActiviteTypes($data->$chaineInformations->patrimoineCulturelType, $languesSite, $objetApidae);
        }*/


        //TODO voir pour créer une classe de prestationActivite
//ActivitePrestation ...
        if($objetApidae->getObjTypeApidae() == "ACTIVITE" || $objetApidae->getObjTypeApidae() == "EQUIPEMENT") {
            if(isset($data->$chaineInformations->activitesSportives)) {
                foreach ($data->$chaineInformations->activitesSportives as $v) {
                    $this->traitementActiviteTypes($v, $languesSite, $objetApidae);
                }
            }
            //TEST prestationActivite
            if(isset($data->prestations->activites)) {
                foreach($data->prestations->activites as $v) {
                    $this->traitementActiviteTypes($v, $languesSite, $objetApidae);
                }
            }
        }

        //-------------------- Portee ----------------------
        if(isset($data->$chaineInformations->portee)) {
            $tab = array();
            $v = $this->traitementReference($data->$chaineInformations->portee->elementReferenceType, $data->$chaineInformations->portee->id);
            if($v != null) {
                $tab['libelle'] = $this->traitementLibelleLangues($languesSite, $v);
                $tab['ordre'] = $v->ordre;
            }
            if(isset($data->ouverture->periodesOuvertures)) {
                $tab['dateFin'] = $data->ouverture->periodesOuvertures[0]->dateFin;
                //echo date('Y-m-d', strtotime($tab['dateFin']));
                //echo date_format(date_create($tab['dateFin']), "Y-m-d");
            }
            if(isset($data->ouverture->periodesOuvertures)) {
                $tab['dateDebut'] = $data->ouverture->periodesOuvertures[0]->dateDebut;
            }
            $objetApidae->setCapacite($tab);
        }

        //-------------------- ObjetsLies ----------------------
        //TODO objetsLies traitement
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
        $this->em->persist($objetApidae);
        $this->em->flush();
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

    private function traitementFamilleCritere($id, $languesSite) {
        $v = $this->traitementReference("FamilleCritere", $id);
        if($v != false) {
            return $this->traitementLibelleLangues($languesSite, $v);
        }
        return "";
    }

    private function traitementServices($tab, $i, $service, $languesSite) {
        if(isset($tab->services[$i]->id)) {
            $v = $this->traitementReference("PrestationService", $tab->services[$i]->id);
            if($v != false) {
                $lib = $this->traitementLibelleLangues($languesSite, $v);
                $service->setSerLibelle($lib);
                $this->traitementServiceDetails($service, $v, $languesSite);
            }
        }

        if(isset($tab->modesPaiement[$i]->id)) {
            $v = $this->traitementReference("ModePaiement", $tab->modesPaiement[$i]->id);
            if($v != false) {
                $lib = $this->traitementLibelleLangues($languesSite, $v);
                $service->setSerLibelle($lib);
                $this->traitementServiceDetails($service, $v, $languesSite);
            }
        }

        if(isset($tab->tourismesAdaptes[$i]->id)) {
            $v = $this->traitementReference("TourismeAdapte", $tab->tourismesAdaptes[$i]->id);
            if($v != false) {
                $lib = $this->traitementLibelleLangues($languesSite, $v);
                $service->setSerLibelle($lib);
                //$this->traitementServiceLibelle($v, $service, $chaineLangue);
                $this->traitementServiceDetails($service, $v, $languesSite);
            }
        }
    }

    private function traitementServiceLibelle($v, $service, $languesSite) {
        $lib = $this->traitementLibelleLangues($languesSite, $v);
        $service->setSerLibelle($lib);
    }

    private function traitementServiceDetails($service, $v, $languesSite) {
        if(isset($v->familleCritere)) {
            $type = $this->traitementFamilleCritere($v->familleCritere->id, $languesSite);
            $service->setSerFamilleCritere($type);
        }
        if(isset($v->description)) {
            $service->setSerInfosSup($v->description);
        }
    }

    private function traitementCategorieDetails($catLibelle, $objetCatRef, $objetApidae) {
        //On vérifie si la catégorie existe déjà
        $catExist = $this->em->getRepository(Categorie::class)->findOneByCatId($objetCatRef->id);
        if($catExist == null) {
            $categorie = new Categorie();
            $this->updateCategorie($catLibelle, $categorie, $objetCatRef, $objetApidae);
            $this->em->persist($categorie);
            $this->em->flush();
        } else {
            $this->updateCategorie($catLibelle, $catExist, $objetCatRef, $objetApidae);
            $this->em->merge($catExist);
            $this->em->merge($objetApidae);
        }
    }

    private function updateCategorie($catLibelle,$categorie, $objetCatRef, $objetApidae) {
        $categorie->setCatId($objetCatRef->id);
        $categorie->setCatLibelle($catLibelle);
        $categorie->setCatRefType($objetCatRef->elementReferenceType);
        if(!$categorie->getObjets()->contains($objetApidae)) {
            //Ajout de lobjet à la catégorie :
            $categorie->addObjet($objetApidae);
        }
        if(!$objetApidae->getCategories()->contains($categorie)) {
            //Associe la catégorie à l'objet :
            $objetApidae->addCategorie($categorie);
        }
    }

    private function traitementTypeCategories($tab, $objetApidae, $languesSite) {
        foreach($tab as $categorie) {
            $v = $this->traitementReference($categorie->elementReferenceType, $categorie->id);
            $lanLib =$this->traitementLibelleLangues($languesSite, $v);
            $this->traitementCategorieDetails($lanLib, $categorie, $objetApidae);
        }
    }

    private function updateMultimedia($multi, $languesSite, $i, $data, $objetApidae, $update) {
        $multi->setIdMultimedia($data->illustrations[$i]->identifiant);
        if(isset($data->illustrations[$i]->nom)) {
            $lib = $this->traitementLibelleLangues($languesSite, $data->illustrations[$i]->nom);
            $multi->setMulLibelle($lib);
        } else {
            $multi->setMulLibelle(null);
        }
        $multi->setMulLocked($data->illustrations[$i]->locked);
        $multi->setMulType($data->illustrations[$i]->type);
        if(isset($data->illustrations[$i]->traductionFichiers[0]->url)) {
            $multi->setMulUrl($data->illustrations[$i]->traductionFichiers[0]->url);
        }
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

        $multi->setObjetApidae($objetApidae);
        if(!$objetApidae->getMultimedias()->contains($multi)) {
            $objetApidae->addMultimedia($multi);
        }
        if($update) {
            $this->em->merge($multi);
        } else {
            $this->em->persist($multi);
        }
    }

    private function updateTraduction($traduction, $data, $chaineLangue, $langueTrad, $objetApidae, $update) {
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
        if(!$objetApidae->getTraductions()->contains($traduction)) {
            //Associe la traduction à l'objet
            $objetApidae->addTraduction($traduction);
        }
        if(!$langueTrad->getTraductions()->contains($traduction)) {
            //AJoute la traduction au dico de la langue
            $langueTrad->addTraduction($traduction);
        }

        if($update) {
            $this->em->merge($traduction);
        } else {
            $traduction->setTraDescriptionPersonnalisee(null);
            $traduction->setTraBonsPlans(null);
            $traduction->setTraInfosSup(null);
            //Associe la langue à la traduction
            $traduction->setLangue($langueTrad);

            //Associe l'objet à la traduction :
            $traduction->setObjet($objetApidae);
            $this->em->persist($traduction);
        }
    }

    private function updateObjetApidae($objetApidae, $data, $selectionApidae,$languesSite, $update) {
        if(isset($data->localisation->geolocalisation)) {
            $geo = $data->localisation->geolocalisation;
            if(isset($geo->geoJson->coordinates)) {
                $coord = $geo->geoJson->coordinates[0]."|".$geo->geoJson->coordinates[1];
                $objetApidae->setObjGeolocalisation($coord);
            } else {
                $objetApidae->setObjGeolocalisation(null);
            }
        } else {
            $objetApidae->setObjGeolocalisation(null);
        }
        $objetApidae->setObjSuggestion(false);
        $objetApidae->setObjDateSuggestion(null);
        $objetApidae->setObjTypeApidae($data->type);
        if(!$selectionApidae->getObjets()->contains($objetApidae)) {
            $selectionApidae->addObjetApidae($objetApidae);
        }
        if(!$objetApidae->getSelectionsApidae($selectionApidae)) {
            $objetApidae->addSelectionApidae($selectionApidae);
        }
        if($update) {
            $this->em->merge($objetApidae);
            $this->em->merge($selectionApidae);

        } else {
            $objetApidae->setIdObjet($data->id);
            $this->em->persist($objetApidae);
            if(!$selectionApidae->getObjets()->contains($objetApidae)) {
                $this->em->merge($selectionApidae);
            }
        }
    }
    private function updateService($service, $objetApidae, $update) {
        if(!$service->getObjetsApidae()->contains($objetApidae)) {
            //Associe le service à la traduction :
            $service->addObjetApidae($objetApidae);
        }
        if(!$objetApidae->getServices()->contains($service)) {
            //Ajoute le service au dico de la traduction :
            $objetApidae->addService($service);
        }
        if($update) {
            $this->em->merge($service);
            $this->em->merge($objetApidae);
        } else {
            $this->em->persist($service);
            $this->em->merge($objetApidae);
        }
    }


    private function updateEquipement($equipement, $objetApidae, $update) {
        if(!$equipement->getObjetsApidae()->contains($objetApidae) ) {
            //Associe l'équipement à la traduction
            $equipement->addObjetApidae($objetApidae);
        }
        if(!$objetApidae->getEquipements()->contains($equipement)) {
            //Ajoute l'équipement au dico de la traduction :
            $objetApidae->addEquipement($equipement);
        }
        if($update) {
            $this->em->merge($equipement);
            $this->em->merge($objetApidae);
        } else {
            $this->em->persist($equipement);
            $this->em->merge($objetApidae);
        }
    }

    private function updateTypeCLient($typeClient, $i, $tab, $languesSite, $objetApidae, $update) {
        $typeClient->setTypId($tab->typesClientele[$i]->id);
        if(isset($tab->typesClientele[$i])) {
            $v = $this->traitementReference($tab->typesClientele[$i]->elementReferenceType, $tab->typesClientele[$i]->id);
            if($v != false) {
                $typeClient->setTypLibelle($this->traitementLibelleLangues($languesSite, $v));
                if(isset($v->familleCritere)) {
                    $v = $this->traitementReference($v->familleCritere->elementReferenceType, $v->familleCritere->id);
                    if($v != null) {
                        $typeClient->setFamilleCritere(($this->traitementLibelleLangues($languesSite, $v)));
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
        if(!$typeClient->getObjetsApidae()->contains($objetApidae)) {
            //Associe lobjet au type de public
            $typeClient->addObjetApidae($objetApidae);
        }
        if(!$objetApidae->getTypesPublic()->contains($typeClient)) {
            //Ajoute le type de client au dico de la traduction :
            $objetApidae->addTypePublic($typeClient);
        }
        if($update) {
            $this->em->merge($typeClient);
        }
        $this->em->persist($typeClient);
        //$this->em->flush();
    }

    public function traitementInfosTarif($tarifType, $tarif, $tab, $objetApidae, $update) {
        $infosTarifs = $this->em->getRepository(InformationsTarif::class)->findOneBy(
            array("objetApidae"=> $objetApidae, "tarifType" => $tarifType));
        if($infosTarifs == null) {
            $infosTarifs = new InformationsTarif();
        }
        $infosTarifs->setTarDevise($tarif->devise);
        if(isset($tarif->maximum)) {
            $infosTarifs->setTarMax($tarif->maximum);
        } else {
            $infosTarifs->setTarMax(null);
        }
        if(isset($tarif->minimum)) {
            $infosTarifs->setTarMin($tarif->minimum);
        } else {
            $infosTarifs->setTarMin(null);
        }

        if(isset($tab->indicationTarif)) {
            $infosTarifs->setTarIndication($tab->indicationTarif);
        } else {
            $infosTarifs->setTarIndication(null);
        }
        $infosTarifs->setObjetApidae($objetApidae);
        $infosTarifs->setTarifType($tarifType);

        if(!$tarifType->getInfosTarif()->contains($infosTarifs)) {
            $tarifType->addInfoTarif($infosTarifs);
        }
        if(!$objetApidae->getInfosTarif()->contains($infosTarifs)) {
            $objetApidae->addInfoTarif($infosTarifs);
        }

        if($update) {
            $this->em->merge($tarifType);
            $this->em->merge($objetApidae);
        } else {
            $this->em->persist($tarifType);
            $this->em->merge($objetApidae);
        }
    }


    public function traitementActiviteTypes($tab, $languesSite, $objetApidae) {
        $act =  $this->em->getRepository(ActiviteType::class)->findOneByIdActiviteType($tab->id);
        $update = true;
        if($act == null) {
            $update = false;
            $act = new ActiviteType();
        }
        $v = $this->traitementReference($tab->elementReferenceType, $tab->id);
        if($v != null) {
            $act->setLibelle($this->traitementLibelleLangues($languesSite, $v));
        }
        $act->setLibelle($this->traitementLibelleLangues($languesSite, $v));
        $act->setIdActivite($v->id);
        $act->setOrdre($v->ordre);
        $act->setRefType($v->elementReferenceType);
        $objetApidae->setActiviteType($act);
        if(!$act->getActivites()->contains($objetApidae)) {
            $act->addActivite($objetApidae);
        }
        if($update) {
            $this->em->merge($objetApidae);
            $this->em->merge($act);
        } else {
            $this->em->persist($act);
        }
    }

    private function traitementLibelleLangues($languesSite, $objet) {
        $chaineFinale= "";
        //pour chaque langue :
        foreach($languesSite as $key => $val) {
            $shortCut = $val[0] . $val[1];
            $lib = "libelle".$shortCut;
            if(isset($objet->$lib)) {
                $chaineFinale .= '@'.$shortCut.':'.$objet->$lib;
            }
        }
        return $chaineFinale.'@';
    }
}
