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

class TraitementController extends Controller
{
	private $em;

    /**lin
     * @Route("/traitement")
     */
    public function traitementAction()
    {
		$this->em = $this->getDoctrine()->getManager();
		$languesSite[0] = "Français";
		$languesSite[1] = "English";

		//Récupération fichiers :
		$export = file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/selections.json");
		$selections_data = json_decode($export);
		foreach($selections_data as $value) {
			$selectionApidae = $this->em->getRepository(SelectionApidae::class)->findOneBySelLibelle($value->libelle->libelleFr);
			if($selectionApidae == null) {
				$selectionApidae = new SelectionApidae();
				$selectionApidae->setIdSelectionApidae($value->id);
				$selectionApidae->setSelLibelle($value->nom);
				$this->em->persist($selectionApidae);
			} else {
				print($selectionApidae->getSelLibelle()."</br>");
				$this->em->merge($selectionApidae);
			}

			foreach($value->objetsTouristiques as $val) {
				print($val->id."</br>");
				//=> $data = aller chercher le bon fichier dans objetsModifies
				$data = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/objets_modifies/objets_modifies-".$val->id.".json"));

				//récupération des données :
				//Traitement de la chaine "type" (pour récupération d'info : notation différente selon le typeApidae)
				$type = $data->type;
				$chaineExplode = explode("_",$type);
				$tab = null;
				foreach ($chaineExplode as $value) {
					$str = strtolower($value);
					$str[0] = strtoupper($str[0]);
					$tab[] = $str;
				}
				$typeObj = implode($tab);
				$chaineInformations = "informations".$typeObj;
				if($data->type == "FETE_ET_MANIFESTATION") {
					$chaineType = "typesManifestation";
				} else {
					$tab[0] = strtolower($tab[0]);
					$chaineType = implode($tab)."Type";
				}

				$this->traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite, $typeObj);

			}
		}
		//---
        return $this->render('ApidaeBundle:Default:traitement.html.twig', array('url' => $data->$chaineInformations));
    }

	private function traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite, $typeObj) {
		$fichierRef = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/elements_reference.json"));

		//-------------------- ObjetApidae ----------------------
		//TODO vérifier l'existence de l'objet (id)
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
			$em = $this->getDoctrine()->getManager();
			if(isset($data->$chaineInformations->$chaineType->libelleFr)) {
				//Accès à libelle "simpelement"
				$cat = $data->$chaineInformations->$chaineType->libelleFr;
				$this->traitementCategorie($cat, $objetApidae);
			} else if (isset($data->$chaineInformations->categories[0]->id)){
				foreach($fichierRef as $v) {
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

			//-------------------- Adresse ----------------------
			$adresse = new Adresse();
			if($adr = $data->localisation->adresse) {
				$adresse->setCodePostal($adr->codePostal);
				if(isset($adr->commune->id)) {
					$communes = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/communes.json"));
					foreach($communes as $commune) {
						if($commune->id == $adr->commune->id) {
							$adresse->setCommune($commune->nom);
							$adresse->setCodeCommune($commune->code);
							break;
						}
					}
				}
				for($i = 1; $i < 5; $i++) {
					$chaine = "adresse".$i;
					if(isset($adr->$chaine)) {
						$adresse->setAdresse($adr->$chaine);
						break;
					}
				}
			}
			//Associe l'adresse à l'objet
			$objetApidae->setObjAdresse($adresse);
			//Ajoute l'objet au dico de l'adresse
			$adresse->addObjetApidae($objetApidae);
			$this->em->persist($adresse);
			$this->em->merge($objetApidae);
			$this->em->flush();




			//------------------------------------------------ Traduction --------------------------------------------------
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
			$traduction->setTraTarifrEnClair(null);
			$traduction->setTraInfosSup(null);

			//Associe la langue à la traduction
			$traduction->setLangue($langueTrad);
			//AJoute la traduction au dico de la langue
			$langueTrad->addTraduction($traduction);
			//Associe la traduction à l'objet
			$objetApidae->addTraduction($traduction);
			//Associe l'objet à la traduction :
			$traduction->setObjet($objetApidae);

			//TODO persist Traduction / ObjetApidae
			$this->em->persist($traduction);
			$this->em->flush();

			//-------------------- Types de Public ----------------------
			if(isset($data->prestations->typesClientele)) {
				$tab = $data->prestations;
				for($i = 0; $i < count($tab->typesClientele); $i++) {
					$typeClient = new TypePublic();
					if(isset($tab->typesClientele[$i]->$chaineLangue)) {
						$typeClient->setTypLibelle($tab->typesClientele[$i]->$chaineLangue);
					} else {
						foreach($fichierRef as $v) {
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
						foreach($fichierRef as $v) {
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
					$em->persist($typeClient);
					$this->em->flush();
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
						foreach($fichierRef as $v) {
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
					$em->persist($com);
					$this->em->flush();
				}
			}
			//-------------------- Equipements ----------------------
			if(isset($data->prestations->conforts)) {
				$tab = $data->prestations;
				for($i = 0; $i < count($tab->conforts); $i++) {
					$equipement = new Equipement();
					if(isset($tab->conforts[$i]->$chaineLangue)) {
						$equipement->setEquLibelle($tab->conforts[$i]->$chaineLangue);
					} else if(isset($tab->conforts[$i]->libelleFr)) {
						$equipement->setEquLibelle($tab->conforts[$i]->libelleFr);
					} else {
						$equipement->setEquLibelle(null);
					}
					if(isset($tab->conforts)) {
						foreach($fichierRef as $v) {
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
				}
			}
			if(isset($data->prestations->equipements)) {
				$tab = $data->prestations;
				for($i = 0; $i < count($tab->equipements); $i++) {
					$equipement = new Equipement();
					foreach($fichierRef as $v) {
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
				}
			}
//TODO libelle service
			//-------------------- Services ----------------------
			//services
			if(isset($data->prestations->services)) {
				$tab = $data->prestations;
				for($i = 0; $i < count($tab->services); $i++) {
					$service = new Service();
					if(isset($tab->services)) {
						$this->traitementServices($tab, $i, $service, $fichierRef, $chaineLangue);
					}
					$service->setSerType($tab->services[$i]->elementReferenceType);
					//Associe le service à la traduction :
					$service->setTraduction($traduction);
					//Ajoute le service au dico de la traduction :
					$traduction->addService($service);
					$this->em->persist($service);
				}
			}

			//modes de paiement :
			if(isset($data->descriptionTarif->modesPaiement)) {
				$tab = $data->descriptionTarif;
				for($i = 0; $i < count($tab->modesPaiement); $i++) {
					$service = new Service();
					if(isset($tab->modesPaiement)) {
						$this->traitementServices($tab, $i, $service, $fichierRef, $chaineLangue);
					}
					$service->setSerType($tab->modesPaiement[$i]->elementReferenceType);
					//Associe le service à la traduction :
					$service->setTraduction($traduction);
					//Ajoute le service au dico de la traduction :
					$traduction->addService($service);
					$this->em->persist($service);
				}
			}

			//Handicap (tourismesAdaptes)
			if(isset($data->prestations->tourismesAdaptes)) {
				$tab = $data->prestations;
				for($i = 0; $i < count($tab->tourismesAdaptes); $i++) {
					$service = new Service();
					if(isset($tab->tourismesAdaptes)) {
						$this->traitementServices($tab, $i, $service, $fichierRef, $chaineLangue);
					}
					$service->setSerType($tab->tourismesAdaptes[$i]->elementReferenceType);
					//Associe le service à la traduction :
					$service->setTraduction($traduction);
					//Ajoute le service au dico de la traduction :
					$traduction->addService($service);
					$this->em->persist($service);
				}
			}

			//TODO ?Langues parlées
			//-------------------- Labels ----------------------
			//labelsQualité
			if(isset($data->$chaineInformations->labels)) {
				$tab = $data->$chaineInformations;
				for($i = 0; $i < count($tab->labels); $i++) {
					$label = new LabelQualite();
					if(isset($tab->labels)) {
						//TODO changer labels
						foreach($fichierRef as $v) {
							if($v->elementReferenceType == $tab->labels[$i]->elementReferenceType
								&& $v->id == $tab->labels[$i]->id) {
								$label->setLabClassement($v->$chaineLangue);
								//libelle du label :
								foreach($fichierRef as $value) {
									if($value->elementReferenceType == $v->typeLabel->elementReferenceType
										&& $value->id == $v->typeLabel->id) {
										$label->setLabLibelle($value->$chaineLangue);
										break;
									}
								}
								break;
							}
						}
					}
					//Ajoute la traduction au dico du label
					$label->addTraduction($traduction);
					//Ajoute le label au dico de la traduction
					$traduction->addLabelQualite($label);
					$this->traitementLabelsQualite($label);
				}
			}
			//étoiles
			if(isset($data->$chaineInformations->classement)) {
				if(isset($data->$chaineInformations->classement)) {
					foreach($fichierRef as $v) {
						if($v->elementReferenceType == $typeObj."Classement" &&
							$v->id == $data->$chaineInformations->classement->id) {
							$objetApidae->setObjEtoile($v->$chaineLangue);
						}
					}
				}
			}

			//-------------------- Tarifs ----------------------
			if(isset($data->descriptionTarif)) {
				$tab = $data->descriptionTarif;
				if(isset($tab->tarifsEnClair)) {
					if(isset($tab->tarifsEnClair->$chaineLangue)) {
						$traduction->setTraTarifrEnClair($tab->tarifsEnClair->$chaineLangue);
					} else if(isset($tab->tarifsEnClair->libelleFr)){
						$traduction->setTraTarifrEnClair($tab->tarifsEnClair->libelleFr);
					} else {
						$traduction->setTraTarifrEnClair(null);
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
							foreach($fichierRef as $v) {
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

			//-------------------- ObjetsLies ----------------------
			//TODO objetsLies
			if(isset($data->liens)) {
				for ($i = 0; $i < count($data->liens); $i++) {
					if(isset($data->liens->objetTouristique[$i]->id)) {
						$objetlie = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($data->liens->objetTouristique[$i]->id);
						if($objetlie != null) {
							$objetApidae->addObjetLie($objetlie);
							//TODO vérifier
							//$objetlie->addObjetLies($objetApidae);
						} else {
							$data = json_decode(file_get_contents("/var/www/local/Symfony/projetApidae/tools/tmp/exportInitial/objets_modifies/objets_modifies-".$data->liens->objetTouristique[$i]->id.".json"));
							//$this->traitementObjetApidae($selectionApidae, $data, $chaineType, $chaineInformations, $languesSite);
						}
					}
				}
			}

			//-------------------- Capacite ----------------------
			//TODO capacite


			//-------------------- Duree ----------------------
			//TODO duree

			$i++;
		}
	}

	private function traitementFamilleCritere($id, $fichierRef, $chaineLangue) {
		foreach($fichierRef as $v) {
			if($v->elementReferenceType == "FamilleCritere" &&
				$v->id == $id) {
				if(isset($v->$chaineLangue)) {
					return $v->$chaineLangue;
				} else if (isset($v->libelleFr)) {
					return $v->libelleFr;
				} else {
					return "Pas de libelle disponible";
				}
			}
		}
	}

	private function traitementServices($tab, $i, $service, $fichierRef, $chaineLangue) {
		foreach($fichierRef as $v) {
			if($v->elementReferenceType == "PrestationService"
				&& isset($tab->services[$i]->id)
				&& $v->id == $tab->services[$i]->id) {
				$service->setSerLibelle($v->$chaineLangue);
				$this->traitementServiceDetails($service, $v, $fichierRef, $chaineLangue);
			} else if($v->elementReferenceType == "ModePaiement"
				&& isset($tab->modesPaiement[$i]->id)
				&& $v->id == $tab->modesPaiement[$i]->id) {
				$service->setSerLibelle($v->$chaineLangue);
				$this->traitementServiceDetails($service, $v, $fichierRef, $chaineLangue);
			} else if($v->elementReferenceType == "TourismeAdapte"
				&& isset($tab->tourismesAdaptes[$i]->id)
				&& $v->id == $tab->tourismesAdaptes[$i]->id) {
				$service->setSerLibelle($v->$chaineLangue);
				$this->traitementServiceDetails($service, $v, $fichierRef, $chaineLangue);
			}
		}
	}

	private function traitementServiceDetails($service, $v, $fichierRef, $chaineLangue) {
		if(isset($v->familleCritere)) {
			$type = $this->traitementFamilleCritere($v->familleCritere->id, $fichierRef, $chaineLangue);
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

}
