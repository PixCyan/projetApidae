<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\Activite;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Commune;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Hebergement;
use ApidaeBundle\Entity\Restaurant;
use ApidaeBundle\Entity\SejourPackage;
use ApidaeBundle\Entity\SelectionApidae;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewTraitement extends ContainerAwareCommand
{
    private $communes;
    private $fichierRef;
    private $total;
    private $languesSite;

    // …
    protected function configure()
    {
        $this
            ->setName('command:test')
            ->setDescription('Traitement des données Apidae');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
        $this->languesSite[0] = "Français";
        $this->languesSite[1] = "English";
        $reinitialisation = true;

        //Récupération fichiers :
        try {
            $export = file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/selections.json");
            $this->communes = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/communes.json"));
            $this->fichierRef = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/elements_reference.json"));
            $selections_data = json_decode($export);
            foreach ($selections_data as $value) {
                $selectionApidae = $em->getRepository(SelectionApidae::class)->findOneByIdSelectionApidae($value->id);
                if ($selectionApidae == null) {
                    $selectionApidae = new SelectionApidae();
                    $selectionApidae->setIdSelectionApidae($value->id);
                    $selectionApidae->setSelLibelle($value->nom);
                    $em->persist($selectionApidae);
                    $em->flush();
                } else {
                    $selectionApidae->setSelLibelle($value->nom);
                    $em->merge($selectionApidae);
                }
                print($selectionApidae->getSelLibelle() . "\n");
                foreach ($value->objetsTouristiques as $val) {
                    print($val->id . "\n");
                    //=> $data = aller chercher le bon fichier dans objetsModifies
                    $data = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/objets_modifies/objets_modifies-" . $val->id . ".json"));
                    if ($data) {
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
                        //$this->traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite);
                    }

                }
            }
            //---
            $output->writeln("Total objet = " . $this->total);
            $output->writeln("Fin de traitement.");
        } catch (Exception $e) {
            $output->writeln("Problème : " . $e->getMessage());
        }
    }


    private function traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations) {
        $em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
        $this->total++;
        $typeObjet = $data->type;
        //-------------------- ObjetApidae ----------------------
        $update = true;
        $objetApidae = null;
        if($typeObjet == "RESTAURATION") {
            $objetApidae = $em->getRepository(Restaurant::class)->findOneBy(['idObj' => $data->id]);
            if(!$objetApidae) {
                $update = false;
                $objetApidae = new Restaurant();
            }
        } else if($typeObjet == "HOTELLERIE"
            || $typeObjet == "HEBERGEMENT_LOCATIF"
            || $typeObjet == "HEBERGEMENT_COLLECTIF"
            || $typeObjet == "HOTELLERIE_PLEIN_AIR" ) {
            $objetApidae = $em->getRepository(Hebergement::class)->findOneBy(['idObj' => $data->id]);
            if(!$objetApidae) {
                $update = false;
                $objetApidae = new Hebergement();
            }
        } else if($typeObjet == "ACTIVITE"
            || $typeObjet == "PATRIMOINE_CULTUREL") {
            $objetApidae = $em->getRepository(Activite::class)->findOneBy(['idObj' => $data->id]);
            if(!$objetApidae) {
                $update = false;
                $objetApidae = new Activite();
            }
        } else if($typeObjet  == "FETE_ET_MANIFESTATION") {
            $objetApidae = $em->getRepository(Evenement::class)->findOneBy(['idObj' => $data->id]);
            if(!$objetApidae) {
                $update = false;
                $objetApidae = new Evenement();
            }
        } else if($typeObjet  == "SEJOUR_PACKAGE") {
            $objetApidae = $em->getRepository(SejourPackage::class)->findOneBy(['idObj' => $data->id]);
            if(!$objetApidae) {
                $update = false;
                $objetApidae = new SejourPackage();
            }
        }
        if($objetApidae) {
            $this->updateObjetApidae($objetApidae, $data, $selectionApidae, $update);
            $this->addAdresse($data, $objetApidae);
        }



    }

    private function addCategories($data, $objetApidae, $chaineInformations) {
        //-------------------- Categories ----------------------
        if(isset($data->$chaineInformations->categories)) {
            foreach($data->$chaineInformations->categories as $categorie) {
                $v = $this->traitementReference($categorie->elementReferenceType,$categorie->id);
                $lanLib = $this->traitementLibelleLangues($languesSite, $v);
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
    }

    private function addAdresse($data, $objetApidae) {
        //-------------------- Adresse - Communes ----------------------
        $em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
        if($adr = $data->localisation->adresse) {
            $objetApidae->setCodePostal($adr->codePostal);
            if(isset($adr->commune->id)) {
                $commune = $em->getRepository(Commune::class)->findOneByComId($adr->commune->id);
                if($commune != null) {
                    $commune->addObjetApidae($objetApidae);
                    $objetApidae->setCommune($commune);
                    //update
                    $em->merge($commune);
                    $em->merge($objetApidae);
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
                    $em->persist($commune);
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
    }

    private function updateObjetApidae($objetApidae, $data, $selectionApidae, $update) {
        $em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
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
            $em->merge($objetApidae);
            $em->merge($selectionApidae);

        } else {
            $objetApidae->setIdObjet($data->id);
            $em->persist($objetApidae);
            if(!$selectionApidae->getObjets()->contains($objetApidae)) {
                $em->merge($selectionApidae);
            }
        }
    }


}
