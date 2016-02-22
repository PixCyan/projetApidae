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

	private function traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite) {
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
					print("cat = famille critère \n");
					if(!$this->em->getRepository(Categorie::class)->findOneByCatId(($v->familleCritere->id))) {
						$val = $this->traitementReference($v->familleCritere->elementReferenceType, $v->familleCritere->id);
						$lanLib =$this->traitementLibelleLangues($languesSite, $val);
						$this->traitementCategorieDetails($lanLib, $v->familleCritere->id, $objetApidae);
					}
				}
			}
		} else if(isset($data->$chaineInformations->typesManifestation)) {
			print("cat = fete \n");
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
			foreach($tab as $value) {
				$v = $this->traitementReference($value->elementReferenceType, $value->id);
				$lanLib = $this->traitementLibelleLangues($languesSite, $v);
				$this->traitementCategorieDetails($lanLib, $value->id, $objetApidae);
			}
		}

		//$this->em->flush();

		//------------------------------------------------ Traduction -------------------------------------------------
		$nom = $this->traitementLibelleLangues($languesSite, $data->nom);
		$objetApidae->setNom($nom);
		$presentation = $data->presentation;
		if(isset($data->presentation)) {
			if (isset($presentation->descriptifCourt)) {
				$objetApidae->setDescriptionCourte($this->traitementLibelleLangues($languesSite, $presentation->descriptifCourt));
			}
			if(isset($presentation->descriptifDetaille)) {
				$objetApidae->setDescriptionLongue($this->traitementLibelleLangues($languesSite,$presentation->descriptifDetaille ));
			}
		}

		$objetApidae->setDescriptionPersonnalisee(null);
		$objetApidae->setBonsPlans(null);
		$objetApidae->setDateEnClair(null);
		$objetApidae->setTarifEnClair(null);
		$objetApidae->setInfosSup(null);

		//-------------------- Types de Public ----------------------
		if(isset($data->prestations->typesClientele)) {
			$tab = $data->prestations;
			for($i = 0; $i < count($tab->typesClientele); $i++) {
				$typeClient = $this->em->getRepository(TypePublic::class)->findOneByTypId(($tab->typesClientele[$i]->id));
				if($typeClient == null) {
					$typeClient = new TypePublic();
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
					//Associe lobjet au type de public
					$typeClient->addObjetApidae($objetApidae);
					//Ajoute le type de client au dico de la traduction :
					$objetApidae->addTypePublic($typeClient);
					$this->em->persist($typeClient);
					//$this->em->flush();
				} else if($this->em->getRepository(TypePublic::class)->findOneByTypId(($tab->typesClientele[$i]->id)) != $typeClient) {
					//Associe la traduction au type de public
					$typeClient->addObjetApidae($objetApidae);
					//Ajoute le type de client au dico de la traduction :
					$objetApidae->addTypePublic($typeClient);
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
					//Associe l'équipement à la traduction
					$equipement->addObjetApidae($objetApidae);
					//Ajoute l'équipement au dico de la traduction :
					$objetApidae->addEquipement($equipement);
					$this->em->persist($equipement);
				} else if($this->em->getRepository(Equipement::class)->findOneByEquId(($tab->equipements[$i]->id)) != $equipement) {
					//Associe l'équipement à la traduction
					$equipement->addObjetApidae($objetApidae);
					//Ajoute l'équipement au dico de la traduction :
					$objetApidae->addEquipement($equipement);
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
						$this->traitementServices($tab, $i, $service, $languesSite);
						$service->setSerType($tab->services[$i]->elementReferenceType);
					}
					//Associe le service à la traduction :
					$service->addObjetApidae($objetApidae);
					//Ajoute le service au dico de la traduction :
					$objetApidae->addService($service);
					$this->em->persist($service);
				} else if($this->em->getRepository(Service::class)->findOneBySerId($tab->services[$i]->id) != $service){
					//Associe le service à la traduction :
					$service->addObjetApidae($objetApidae);
					//Ajoute le service au dico de la traduction :
					$objetApidae->addService($service);
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
						$this->traitementServices($tab, $i, $service, $languesSite);
						$service->setSerType($tab->modesPaiement[$i]->elementReferenceType);
					}
					//Associe le service à la traduction :
					$service->addObjetApidae($objetApidae);
					//Ajoute le service au dico de la traduction :
					$objetApidae->addService($service);
					$this->em->persist($service);
				} else if($this->em->getRepository(Service::class)->findOneBySerId(($tab->modesPaiement[$i]->id)) != $service){
					//Associe le service à la traduction :
					$service->addObjetApidae($objetApidae);
					//Ajoute le service au dico de la traduction :
					$objetApidae->addService($service);
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
						$this->traitementServices($tab, $i, $service, $languesSite);
						$service->setSerType($tab->tourismesAdaptes[$i]->elementReferenceType);
					}
					//Associe le service à la traduction :
					$service->addObjetApidae($objetApidae);
					//Ajoute le service au dico de la traduction :
					$objetApidae->addService($service);
					$this->em->persist($service);
				} else if($this->em->getRepository(Service::class)->findOneBySerId($tab->tourismesAdaptes[$i]->id) != $service){
					//Associe le service à la traduction :
					$service->addObjetApidae($objetApidae);
					//Ajoute le service au dico de la traduction :
					$objetApidae->addService($service);
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
					if($objetApidae->getLabelsQualite() != null && !$objetApidae->getLabelsQualite()->contains($label)) {
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
			$tab = $data->descriptionTarif;
			if(isset($tab->tarifsEnClair)) {
				if(isset($tab->tarifsEnClair)) {
					$lib = $this->traitementLibelleLangues($languesSite, $tab->tarifsEnClair);
					$objetApidae->setTarifEnClair($lib);
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
					if(isset($tarifs->tarifs[$i]->type)) {
						$v = $this->traitementReference($tarifs->tarifs[$i]->type->elementReferenceType, $tarifs->tarifs[$i]->type->id);
						if($v != false) {
							$tarif->setTarLibelle($this->traitementLibelleLangues($languesSite, $v));
						}
					}
					if(isset($tab->indicationTarif)) {
						$tarif->setTarIndication($tab->indicationTarif);
					} else {
						$tarif->setTarIndication(null);
					}
					//Associe le tarif à la traduction :
					$tarif->setObjetApidae($objetApidae);
					//Ajoute le tarif à la traduction :
					$objetApidae->addTarif($tarif);
					$this->em->persist($tarif);
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
				$multi = new Multimedia();
				if(isset($data->illustrations[$i]->nom)) {
					$lib = $this->traitementLibelleLangues($languesSite, $data->illustrations[$i]->nom);
					$multi->setMulLibelle($lib);
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
				$multi->setObjetApidae($objetApidae);
				//Ajoute le média au dico de la traduction :
				$objetApidae->addMultimedia($multi);
				//print($multi->getMulLibelle()."</br>");
				$this->em->persist($multi);
			}
		}

		//-------------------- Capacite ----------------------
		//TODO capacite



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

		//TODO vérifier
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
			$categorie->setCatId($id);
			$categorie->setCatLibelle($cat);
			//Associe la catégorie à l'objet :
			$objetApidae->addCategorie($categorie);
			//Ajout de lobjet à la catégorie :
			$categorie->addObjet($objetApidae);
			$this->em->persist($categorie);
			$this->em->flush();
		} else if($this->em->getRepository(ObjetApidae::class)->findOneByIdObj($objetApidae->getIdObjet()) != $objetApidae){
			//Associe la catégorie à l'objet
			$objetApidae->addCategorie($catExist);
			$catExist->addObjet($objetApidae);
			$this->em->merge($catExist);
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
}
