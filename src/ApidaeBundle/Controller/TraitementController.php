<?php

namespace ApidaeBundle\Controller;

use ApidaeBundle\Entity\SelectionApidae;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\TraductionObjetApidae;
use ApidaeBundle\Entity\Adresse;
use ApidaeBundle\Entity\LabelQualite;
use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\Equipement;
use ApidaeBundle\Entity\Service;
use ApidaeBundle\Entity\MoyenCommunication;
use ApidaeBundle\Entity\Multimedia;
use ApidaeBundle\Entity\Tarif;
use ApidaeBundle\Entity\Ouverture;
use ApidaeBundle\Entity\TypePublic;
use ApidaeBundle\Entity\ObjetLie;
use ApidaeBundle\Entity\Commune;

class TraitementController extends Controller
{
	private $em;
	private $communes;
	private $fichierRef;

	public function traitementAction()
	{
	//$this->em = $this->getDoctrine()->getManager();
		$this->em = $this->getDoctrine()->getManager();
		$languesSite[0] = "Français";
		$languesSite[1] = "English";

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
					print($selectionApidae->getSelLibelle() . "\n");
					$selectionApidae->setSelLibelle($value->nom);
					$this->em->merge($selectionApidae);
				}
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
		} catch(Exception $e) {
			$output->writeln("Problème : ".$e->getMessage());
		}
	}

	private function traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite) {
		//-------------------- ObjetApidae ----------------------
		$objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($data->id);
		if($objetApidae == null) {
			//TODO test restautant
			if($selectionApidae->libelle->libelleFr == "Restaurants") {
				$objetApidae = new Restaurant();
			}
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
		if(isset($data->$chaineInformations->categories)) {
			foreach($data->$chaineInformations->categories as $categorie) {
				$v = $this->traitementReference($categorie->elementReferenceType,$categorie->id);
				$lanLib =$this->traitementLibelleLangues($languesSite, $v);
				$this->traitementCategorieDetails($lanLib, $categorie->id, $objetApidae);
				if(isset($v->familleCritere)) {
					if(!$this->em->getRepository(Categorie::class)->findOneByCatId(($v->familleCritere->id))) {
						$val = $this->traitementReference($v->familleCritere->elementReferenceType, $v->familleCritere->id);
						$lanLib =$this->traitementLibelleLangues($languesSite, $val);
						$this->traitementCategorieDetails($lanLib, $v->familleCritere->id, $objetApidae);
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
					$catExist = $this->em->getRepository(Categorie::class)->findOneByCatId($value->id);
					if($catExist != null) {
						$v = $this->traitementReference($value->elementReferenceType, $value->id);
						$lanLib = $this->traitementLibelleLangues($languesSite, $v);
						$this->traitementCategorieDetails($lanLib, $value->id, $objetApidae);
					}
				}
			} else {
				$tab = $data->$chaineInformations->$chaineType;
				$catExist = $this->em->getRepository(Categorie::class)->findOneByCatId($tab->id);
				if($catExist != null) {
					$v = $this->traitementReference($tab->elementReferenceType, $tab->id);
					$lanLib = $this->traitementLibelleLangues($languesSite, $v);
					$this->traitementCategorieDetails($lanLib, $tab->id, $objetApidae);
				}
			}
		}

		//$this->em->flush();

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
				$typeClient = $this->em->getRepository(TypePublic::class)->findOneByTypId(($tab->typesClientele[$i]->id));
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
				$com = new MoyenCommunication();
				if(isset($tab->moyensCommunication[$i])) {
					$v = $this->traitementReference($tab->moyensCommunication[$i]->type->elementReferenceType, $tab->moyensCommunication[$i]->type->id);
					$lib = $this->traitementLibelleLangues($languesSite, $v);
					$com->setMoyComLibelle($lib);
				}
				$com->setMoyComCoordonnees($tab->moyensCommunication[$i]->coordonnees->fr);
				//associe la traduction à l'objet
				$com->setObjetApidae($objetApidae);
				//Ajoute le moyen de communication au dico de la traduction :
				$objetApidae->addMoyenCommunication($com);
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
						print("Obj :".$objetApidae->getId()."\n");
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
			//TODO changer infor tarif et tarifType

			$tab = $data->descriptionTarif;
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
					//TODO terminer
					$this->traitementInfosTarif($tarifType,$tarifs->tarifs[$i], $tab, $objetApidae, $update);




					/*$tarif = new Tarif();
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
                        $v = $this->traitementReference($tarifs->tarifs[$i]->type->elementReferenceType, $tarifs->tarifs[$i]->type->id);
                        if($v != false) {
                            $tarif->setTarLibelle($v->$chaineLangue);
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
                    $this->em->persist($tarif);*/
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
					$ouverture = new Ouverture();
					$ouverture->setOuvDateDebut($tab->periodesOuvertures[$i]->dateDebut);
					$ouverture->setOuvDateFin($tab->periodesOuvertures[$i]->dateFin);
					if(isset($tab->periodesOuvertures[$i]->complementHoraire)) {
						$ouverture->setSerInfosSup($this->traitementLibelleLangues($languesSite, $tab->periodesOuvertures[$i]->complementHoraire));
					}
					//Associe l'ouverture à la traduction :
					$ouverture->setObjetApidae($objetApidae);
					//Ajoute l'ouverture au dico de la traduction :
					$objetApidae->addOuverture($ouverture);
					$this->em->persist($ouverture);
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
		//TODO capacite
		//Test
		if(isset($data->$chaineInformations->capacite)) {
			$objetApidae->setCapacite($data->$chaineInformations->capacite);
		}

		//-------------------- Duree ----------------------
		//TODO duree


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

	private function traitementCategorieDetails($cat, $id, $objetApidae) {
		//On vérifie si la catégorie existe déjà
		$catExist = $this->em->getRepository(Categorie::class)->findOneByCatId($id);
		if($catExist == null) {
			$categorie = new Categorie();
			$this->updateCategorie($cat, $categorie, $id, $objetApidae);
			$this->em->persist($categorie);
			//$this->em->flush();
		} else {
			$this->updateCategorie($cat, $catExist, $id, $objetApidae);
			$this->em->merge($catExist);
			$this->em->merge($objetApidae);
		}
	}

	private function updateCategorie($cat,$categorie, $id, $objetApidae) {
		$categorie->setCatId($id);
		$categorie->setCatLibelle($cat);
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
			$this->traitementCategorieDetails($lanLib, $categorie->id, $objetApidae);
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
		return $chaineFinale;
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
		if($update) {
			$this->em->merge($traduction);
		} else {
			$traduction->setTraDescriptionPersonnalisee(null);
			$traduction->setTraBonsPlans(null);
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
		if(isset($data->descriptionTarif)) {
			$tab = $data->descriptionTarif;
			if (isset($tab->tarifsEnClair)) {
				if (isset($tab->tarifsEnClair)) {
					$lib = $this->traitementLibelleLangues($languesSite, $tab->tarifsEnClair);
					$objetApidae->setTarifEnClair($lib);
				}
			}
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
		if($update == true) {
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
}
