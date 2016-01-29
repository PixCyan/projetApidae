<?php

namespace ApidaeBundle\Controller;

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
		//----------------------------------- Test API
		$this->em = $this->getDoctrine()->getManager();
		$languesSite[0] = "Français";
		$languesSite[1] = "English";

    	//Parcours API
    	/*$apiKey = '4oqV1oVV';
		$projetId = '1464'; //sera $id
		$objId = '105051';
		$requete = array();
		$requete['apiKey'] = $apiKey;
		$requete['projetId'] = $projetId;
		$url = 'http://api.sitra-tourisme.com/api/v002/objet-touristique/get-by-id/';
		$url .= $objId;
		$url .= '?';
		$url .= 'apiKey='.urlencode($apiKey);
		$url .= '&projetId='.urlencode($projetId);
		$url .= "&responseFields=@all&locales=fr,en";
		//$url .= 'query='.urlencode(json_encode($requete));

		$content = file_get_contents($url);
		$data = json_decode($content);*/
		//-------------------------------------
		//Récupération fichiers :
		$export = file_get_contents("/exportInitial/selections.json");
		$selections_data = json_decode($export);
		foreach($selections_data as $value) {
			$selectionApidae = $value->nom;
			foreach($value as $val) {
				//=> recup id
				$id = $val->id;
				//=> $data = aller chercher le bon fichier dans objetsModifies
				$data = json_decode(file_get_contents("/exportInitial/objets_modifies-".$id));



				//récupération des données :
				//Traitement de la chaine "type" (pour récupération d'info : notation différente selon le typeApidae)
				$type = $data->type;
				$chaineExplode = explode("_",$type);
				foreach ($chaineExplode as $value) {
					$str = strtolower($value);
					$str[0] = strtoupper($str[0]);
					$tab[] = $str;
				}
				$chaineInformations = "informations".implode($tab);
				if($data->type == "FETE_ET_MANIFESTATION") {
					$chaineType = "typesManifestation";
				} else {
					$tab[0] = strtolower($tab[0]);
					$chaineType = implode($tab)."Type";
				}

				//-------------------- ObjetApidae ----------------------
				//TODO vérifier l'existence de l'objet (id)
				$objetApidae = $this->em->getRepository(ObjetApidae::class)->findOneByIdObj($data->id);
				/*$cat = $objetApidae->getCategories();
				foreach($cat as $value) {
					print("test : ".$value->getCatLibelle());
				}*/
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
				}

				//-------------------- Categories ----------------------
				//Récupération de la/des catégorie(s)
				$em = $this->getDoctrine()->getManager();
				if(isset($data->$chaineInformations->$chaineType->libelleFr)) {
					//Accès à libelle "simpelement"
					$cat = $data->$chaineInformations->$chaineType->libelleFr;
					$this->traitementCategorie($cat, $objetApidae);
				} else if (isset($data->$chaineInformations->categories[0]->libelleFr)){
					//Accès à libelles dans des tableaux
					$cat = $data->$chaineInformations->categories[0]->libelleFr;
					$this->traitementCategorie($cat, $objetApidae);

					if(isset($data->$chaineInformations->categories[0]->familleCritere)) {
						$famille = $data->$chaineInformations->categories[0]->familleCritere->libelleFr;
						$this->traitementCategorie($famille, $objetApidae);
					}

					//$chaineType .= "[0]";
					if(isset($data->$chaineInformations->typesManifestation[0]->libelleFr)) {
						$type = $data->$chaineInformations->typesManifestation[0]->libelleFr;
						$this->traitementCategorie($type, $objetApidae);
					}
				}

				//-------------------- Adresse ----------------------
				$adresse = new Adresse();
				if($adr = $data->localisation->adresse) {
					$adresse->setCodePostal($adr->codePostal);
					$adresse->setCommune($adr->commune->nom);
					$adresse->setCodeCommune($adr->commune->code);
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
				//$this->em->flush();


				//--------------------Langue ----------------------
				//TODO finir
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

					//print($traduction->getLangue()->getLanLibelle());

					//TODO persist Traduction / ObjetApidae
					$this->em->persist($traduction);
					//$this->em->flush();

					//-------------------- Types de Public ----------------------
					if(isset($data->prestations->typesClientele)) {
						$tab = $data->prestations;
						for($i = 0; $i < count($tab->typesClientele); $i++) {
							$typeClient = new TypePublic();
							if(isset($tab->typesClientele[$i]->$chaineLangue)) {
								$typeClient->setTypLibelle($tab->typesClientele[$i]->$chaineLangue);
							} else {
								$typeClient->setTypLibelle($tab->typesClientele[$i]->libelleFr);
							}
							if(isset($tab->typesClientele[$i]->familleCritere->$chaineLangue)) {
								$typeClient->setFamilleCritere($tab->typesClientele[$i]->familleCritere->$chaineLangue);
							} else if(isset($tab->typesClientele[$i]->familleCritere->libelleFr)) {
								$typeClient->setFamilleCritere($tab->typesClientele[$i]->familleCritere->libelleFr);
							} else {
								$typeClient->setFamilleCritere(null);
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
							//$this->em->flush();
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
								$com->setMoyComLibelle($tab->moyensCommunication[$i]->type->libelleFr);
							}
							$com->setMoyComCoordonnees($tab->moyensCommunication[$i]->coordonnees->fr);
							//associe la traduction à l'objet
							$com->setTraduction($traduction);
							//Ajoute le moyen de communication au dico de la traduction :
							$traduction->addMoyenCommunication($com);
							$em->persist($com);
							//$this->em->flush();
						}
					}
					//-------------------- Equipements ----------------------
					if(isset($data->prestations->conforts)) {
						$tab = $data->prestations;
						for($i = 0; $i < count($tab->conforts); $i++) {
							$equipement = new Equipement();
							if(isset($tab->conforts[$i]->$chaineLangue)) {
								$equipement->setEquLibelle($tab->conforts[$i]->$chaineLangue);
							} else {
								$equipement->setEquLibelle($tab->conforts[$i]->libelleFr);
							}
							if(isset($tab->conforts[$i]->familleCritere)) {
								if(isset($tab->conforts[$i]->familleCritere->$chaineLangue)) {
									$equipement->setEquType($tab->conforts[$i]->familleCritere->$chaineLangue);
								} else if($tab->conforts[$i]->familleCritere->libelleFr) {
									$equipement->setEquType($tab->conforts[$i]->familleCritere->libelleFr);
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
							if(isset($tab->equipements[$i]->$chaineLangue)) {
								$equipement->setEquLibelle($tab->equipements[$i]->$chaineLangue);
							} else {
								$equipement->setEquLibelle($tab->equipements[$i]->libelleFr);
							}
							if(isset($tab->equipements[$i]->familleCritere)) {
								if(isset($tab->equipements[$i]->familleCritere->$chaineLangue)) {
									$equipement->setEquType($tab->equipements[$i]->familleCritere->$chaineLangue);
								} else if($tab->equipements[$i]->familleCritere->libelleFr) {
									$equipement->setEquType($tab->equipements[$i]->familleCritere->libelleFr);
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

					//-------------------- Services ----------------------
					//services
					if(isset($data->prestations->services)) {
						$tab = $data->prestations;
						for($i = 0; $i < count($tab->services); $i++) {
							$service = new Service();
							if(isset($tab->services[$i]->$chaineLangue)) {
								$service->setSerLibelle($tab->services[$i]->$chaineLangue);
							} else {
								$service->setSerLibelle($tab->services[$i]->libelleFr);
							}
							$service->setSerType($tab->services[$i]->elementReferenceType);
							if(isset($tab->services[$i]->familleCritere->$chaineLangue)) {
								if(isset($tab->services[$i]->familleCritere->$chaineLangue)) {
									$service->setSerFamilleCritere($tab->services[$i]->familleCritere->$chaineLangue);
								} else if(isset($tab->services[$i]->familleCritere->libelleFr)) {
									$service->setSerFamilleCritere($tab->services[$i]->familleCritere->libelleFr);
								}
							} else {
								$service->setSerFamilleCritere(null);
							}
							if(isset($tab->services[$i]->description)) {
								$service->setSerInfosSup($tab->services[$i]->description);
							} else {
								$service->setSerInfosSup(null);
							}
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
							if(isset($tab->modesPaiement[$i]->$chaineLangue)) {
								$service->setSerLibelle($tab->modesPaiement[$i]->$chaineLangue);
							} else {
								$service->setSerLibelle($tab->modesPaiement[$i]->libelleFr);
							}
							$service->setSerType($tab->modesPaiement[$i]->elementReferenceType);
							if(isset($tab->modesPaiement[$i]->familleCritere)) {
								if(isset($tab->modesPaiement[$i]->familleCritere->$chaineLangue)) {
									$service->setSerFamilleCritere($tab->modesPaiement[$i]->familleCritere->$chaineLangue);
								} else if(isset($tab->modesPaiement[$i]->familleCritere->libelleFr)) {
									$service->setSerFamilleCritere($tab->modesPaiement[$i]->familleCritere->libelleFr);
								}
							} else {
								$service->setSerFamilleCritere(null);
							}
							if($cond = isset($tab->modesPaiement[$i]->conditions) && ($tab->modesPaiement[$i]->conditions != null)) {
								$service->setSerInfosSup($tab->modesPaiement[$i]->conditions->$chaineLangue);
							} else {
								$service->setSerInfosSup(null);
							}
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
							$service->setSerLibelle($tab->tourismesAdaptes[$i]->$chaineLangue);
							$service->setSerType($tab->tourismesAdaptes[$i]->elementReferenceType);
							if(isset($tab->tourismesAdaptes[$i]->familleCritere)) {
								if(isset($tab->tourismesAdaptes[$i]->familleCritere->$chaineLangue)) {
									$service->setSerFamilleCritere($tab->tourismesAdaptes[$i]->familleCritere->$chaineLangue);
								} else if(isset($tab->tourismesAdaptes[$i]->familleCritere->libelleFr)) {
									$service->setSerFamilleCritere($tab->tourismesAdaptes[$i]->familleCritere->libelleFr);
								}
							} else {
								$service->setSerFamilleCritere(null);
							}
							if(isset($tab->tourismesAdaptes[$i]->familleCritere->description)) {
								$service->setSerInfosSup($tab->tourismesAdaptes[$i]->familleCritere->description);
							} else {
								$service->setSerInfosSup(null);
							}
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
							//print($tab->labels[$i]->typeLabel->libelleFr);
							$label = new LabelQualite();
							if(isset($tab->labels[$i]->$chaineLangue)) {
								$label->setLabClassement($tab->labels[$i]->$chaineLangue);
							} else {
								$label->setLabClassement($tab->labels[$i]->libelleFr);
							}
							if(isset($tab->labels[$i]->typeLabel->$chaineLangue)) {
								$label->setLabLibelle($tab->labels[$i]->typeLabel->$chaineLangue);
							} else {
								$label->setLabLibelle($tab->labels[$i]->typeLabel->libelleFr);
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
						if(isset($data->$chaineInformations->classement->$chaineLangue)) {
							$objetApidae->setObjEtoile($data->$chaineInformations->classement->$chaineLangue);
						} else if($data->$chaineInformations->classement->libelleFr) {
							$objetApidae->setObjEtoile($data->$chaineInformations->classement->libelleFr);
						}
					}

					//-------------------- Tarifs ----------------------
					if(isset($data->descriptionTarif)) {
						$tab = $data->descriptionTarif;
						if(isset($tab->tarifsEnClair)) {
							if(isset($tab->tarifsEnClair->$chaineLangue)) {
								$traduction->setTraTarifrEnClair($tab->tarifsEnClair->$chaineLangue);
							} else {
								$traduction->setTraTarifrEnClair($tab->tarifsEnClair->libelleFr);
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
								} else if(isset($tarifs->tarifs[$i]->type->libelleFr)) {
									$tarif->setTarLibelle($tarifs->tarifs[$i]->type->libelleFr);
								}

								$tarif->setTarIndication($tab->indicationTarif);
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
							} else {
								$traduction->setTraDateEnClair($data->ouverture->periodeEnClair->libelleFr);
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
							$multi->setMulUrlListe($data->illustrations[$i]->traductionFichiers[0]->urlListe);
							$multi->setMulUrlFiche($data->illustrations[$i]->traductionFichiers[0]->urlFiche);
							$multi->setMulUrlDiapo($data->illustrations[$i]->traductionFichiers[0]->urlDiaporama);
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



					//-------------------- Capacite ----------------------
					//TODO capacite


					//-------------------- Duree ----------------------
					//TODO duree


					//-------------------- SelectionApidae ----------------------
					//TODO selectionApidae

					$i++;
				}
			}
		}
		//---
        return $this->render('ApidaeBundle:Default:traitement.html.twig', array('url' => $data->$chaineInformations));


    }


	private function traitementLabelsQualite($label) {
		if($this->em->getRepository(LabelQualite::class)->findOneByLabLibelle($label) == null) {
			$this->em->persist($label);
		}
	}

	private function traitementCategorie($cat, $objetApidae) {
		//On vérifie si la catégorie existe déjà
		//TODO vérifier :
		$catExist = $this->em->getRepository(Categorie::class)->findOneByCatLibelle($cat);
		if($catExist == null) {
			$categorie = new Categorie();
			$categorie->setCatLibelle($cat);
			//Associe la catégorie à l'objet :
			$objetApidae->addCategorie($categorie);
			//Ajout de lobjet à la catégorie :
			$categorie->addObjet($objetApidae);
			$this->em->merge($categorie);
		} else {
			//Associe la catégorie à l'objet
			$objetApidae->addCategorie($catExist);
			$catExist->addObjet($objetApidae);
		}
	}

}
