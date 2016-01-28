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
		$this->em = $this->getDoctrine()->getManager();
		$languesSite[2] = ["Fr", "En"];

    	//Parcours API
    	$apiKey = '4oqV1oVV';
		$projetId = '1464'; //sera $id
		$objId = '119635';
		$requete = array();
		$requete['apiKey'] = $apiKey;
		$requete['projetId'] = $projetId;
		$url = 'http://api.sitra-tourisme.com/api/v002/objet-touristique/get-by-id/';
		$url .= $objId;
		$url .= '?';
		$url .= 'apiKey='.urlencode($apiKey);
		$url .= '&projetId='.urlencode($projetId);
		$url .= "&responseFields=@all";
		//$url .= 'query='.urlencode(json_encode($requete));

		$content = file_get_contents($url);
		$data = json_decode($content);
		//613431
		//376104
		//379730
		//102916
		//113292
		//114156
		//119635

		//récupération des données :
		//-------------------- ObjetApidae ----------------------
		$objetApidae = new ObjetApidae();
		$objetApidae->setIdObjet($data->id);
		if($geo = isset($data->geolocalisation)) {
			$objetApidae->setObjGeolocalisation(isset($geo->geoJson->coordinates));
		} else {
			$objetApidae->setObjGeolocalisation(null);
		}

		//-------------------- Categories ----------------------
		//Traitement de la chaine "type" (différente selon le typeApidae)
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

		//print $chaineInformations;
		//print $chaineType;

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
			if(isset($adr->adresse1)) {
				$adresse->setAdresse($adr->adresse1);
			}
		}
		//Associe l'adresse à l'objet
		$objetApidae->setObjAdresse($adresse);
		//Ajoute l'objet au dico de l'adresse
		$adresse->addObjetApidae($objetApidae);

		//------------------------------------------------ Traduction --------------------------------------------------
		$traduction = new TraductionObjetApidae();
		$traduction->setTraNom($data->nom);

		//Presentation
		if(isset($data->presentation)) {
			$presentation = $data->presentation;
			if(isset($presentation->descriptifCourt->libelleFr)) {
				$traduction->setTraDescriptionCourte($presentation->descriptifCourt->libelleFr);
			}
			if(isset($presentation->descriptifDetaille->libelleFr)) {
				$traduction->setTraDescriptionLongue($presentation->descriptifDetaille->libelleFr);
			} else {
				$traduction->setTraDescriptionLongue(null);
			}
		}
		$traduction->setTraDescriptionPersonnalisee(null);

		//-------------------- Types de Public ----------------------
		if(isset($data->prestations->typesClientele)) {
			$tab = $data->prestations;
			for($i = 0; $i < count($tab->typesClientele); $i++) {
				$typeClient = new TypePublic();
				$typeClient->setTypLibelle($tab->typesClientele[$i]->libelleFr);
				if(isset($tab->typesClientele[$i]->familleCritere->libelleFr)) {
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
				//$em->persist($typeClient);
			}
		}

		//-------------------- Moyens de Communication ----------------------
		if(isset($data->informations->moyensCommunication)) {
			$tab = $data->informations;
			for($i = 0; $i < count($tab->moyensCommunication); $i++) {
				$com = new MoyenCommunication();
				$com->setMoyComLibelle($tab->moyensCommunication[$i]->type->libelleFr);
				$com->setMoyComCoordonnees($tab->moyensCommunication[$i]->coordonnees);
				//associe la traduction à l'objet
				$com->setTraduction($traduction);
				//Ajoute le moyen de communication au dico de la traduction :
				$traduction->addMoyenCommunication($com);
				//$em->persist($com);
			}
		}

		//-------------------- Equipements ----------------------
		if(isset($data->prestations->conforts)) {
			$tab = $data->prestations;
			for($i = 0; $i < count($tab->conforts); $i++) {
				$equipement = new Equipement();
				$equipement->setEquLibelle($tab->conforts[$i]->libelleFr);
				if(isset($tab->conforts[$i]->familleCritere)) {
					$equipement->setEquType($tab->conforts[$i]->familleCritere->libelleFr);
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
				//$this->em->persist($equipement);
			}
		}
		if(isset($data->prestations->equipements)) {
			$tab = $data->prestations;
			for($i = 0; $i < count($tab->equipements); $i++) {
				$equipement = new Equipement();
				$equipement->setEquLibelle($tab->equipements[$i]->libelleFr);
				if(isset($tab->equipements[$i]->familleCritere)) {
					$equipement->setEquType($tab->equipements[$i]->familleCritere->libelleFr);
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
				//$this->em->persist($equipement);
			}
		}

		//-------------------- Services ----------------------
		if(isset($data->prestations->services)) {
			$tab = $data->prestations;
			for($i = 0; $i < count($tab->services); $i++) {
				$service = new Service();
				$service->setSerLibelle($tab->services[$i]->libelleFr);
				$service->setSerType($tab->services[$i]->elementReferenceType);
				if(isset($tab->services[$i]->familleCritere->libelleFr)) {
					$service->setSerFamilleCritere($tab->services[$i]->familleCritere->libelleFr);
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
				//$this->em->persist($service);
			}
		}
		//modes de paiement :
		if(isset($data->descriptionTarif->modesPaiement)) {
			$tab = $data->descriptionTarif;
			for($i = 0; $i < count($tab->modesPaiement); $i++) {
				$service = new Service();
				$service->setSerLibelle($tab->modesPaiement[$i]->libelleFr);
				$service->setSerType($tab->modesPaiement[$i]->elementReferenceType);
				if(isset($tab->modesPaiement[$i]->familleCritere)) {
					$service->setSerFamilleCritere($tab->modesPaiement[$i]->familleCritere->libelleFr);
				} else {
					$service->setSerFamilleCritere(null);
				}
				if($cond = isset($tab->modesPaiement[$i]->conditions) && ($tab->modesPaiement[$i]->conditions != null)) {
					$service->setSerInfosSup($tab->modesPaiement[$i]->conditions->libelleFr);
				} else {
					$service->setSerInfosSup(null);
				}
				//Associe le service à la traduction :
				$service->setTraduction($traduction);
				//Ajoute le service au dico de la traduction :
				$traduction->addService($service);
				//$this->em->persist($service);
			}
		}

		//TODO handicap

		//TODO ?Langues parlées

		//-------------------- Labels ----------------------
		if(isset($data->$chaineInformations->labels)) {
			$tab = $data->$chaineInformations;
			for($i = 0; $i < count($tab->labels); $i++) {
				//print($tab->labels[$i]->typeLabel->libelleFr);
				$label = new LabelQualite();
				$label->setLabClassement($tab->labels[$i]->libelleFr);
				$label->setLabLibelle($tab->labels[$i]->typeLabel->libelleFr);
				//Ajoute l'objet apidae au dico du label
				$label->addObjet($objetApidae);
				//Ajoute le label au dico de l'objet apidae
				$objetApidae->addLabelQualite($label);
				$this->traitementLabelsQualite($label);
			}
		}

		//-------------------- Tarifs ----------------------
		if(isset($data->descriptionTarif)) {
			$tab = $data->descriptionTarif;
			if(isset($tab->tarifsEnClair)) {
				$traduction->setTraTarifrEnClair($tab->tarifsEnClair);
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
					$tarif->setTarLibelle($tarifs->tarifs[$i]->type->libelleFr);
					$tarif->setTarIndication($tab->indicationTarif);
					//Associe le tarif à la traduction :
					$tarif->setTraduction($traduction);
					//Ajoute le tarif à la traduction :
					$traduction->addTarif($tarif);
					//$this->em->persist($tarif);
				}
			}
		}

		//-------------------- Ouvertures ----------------------
		if(isset($data->ouverture)) {
			if(isset($data->ouverture->periodeEnClair)) {
				$traduction->setTraDateEnClair($data->ouverture->periodeEnClair);
			}
			if(isset($data->ouverture->periodesOuvertures)) {
				$tab = $data->ouverture;
				for($i = 0; $i < count($tab->periodesOuvertures); $i++) {
					$ouverture = new Ouverture();
					$ouverture->setOuvDateDebut($tab->periodesOuvertures[$i]->dateDebut);
					$ouverture->setOuvDateFin($tab->periodesOuvertures[$i]->dateFin);
					if(isset($tab->periodesOuvertures[$i]->complementHoraire)) {
						$ouverture->setSerInfosSup($tab->periodesOuvertures[$i]->complementHoraire->libelleFr);
					}
					//Associe l'ouverture à la traduction :
					$ouverture->setTraduction($traduction);
					//Ajoute l'ouverture au dico de la traduction :
					$traduction->addOuverture($ouverture);
					//$this->em->persist($ouverture);
				}
			}
		}

		//-------------------- Multimedias ----------------------
		if(isset($data->illustrations)) {
			for($i = 0; $i < count($data->illustrations); $i++) {
				$multi = new Multimedia();
				if(isset($data->illustrations[$i]->nom->libelleFr)) {
					$multi->setMulLibelle($data->illustrations[$i]->nom->libelleFr);
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
				//$this->em->persist($multi);
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



		//--------------------Langue ----------------------
		//TODO revoir enregistrement des langues avant
		$langue = new Langue();
		$langue->setLanLibelle("Français");
		$langue->setLanShortCut("FR");
		$langue->setLanIso("?");
		//Associe la langue à la traduction
		$traduction->setLangue($langue);
		//AJoute la traduction au dico de la langue
		$langue->addTraduction($traduction);
		//Associe la traduction à l'objet
		$objetApidae->addTraduction($traduction);


		//---
        return $this->render('ApidaeBundle:Default:traitement.html.twig', array('url' => $data->$chaineInformations));
    }

	//TODO persist Traduction / ObjetApidae

	private function traitementLabelsQualite($label) {
		if($this->em->getRepository(LabelQualite::class)->findOneByLabLibelle($label) == null) {
			//$this->em->persist($label);
		}
	}

	private function traitementCategorie($cat, $objetApidae) {
		//On vérifie si la catégorie existe déjà
		if($this->em->getRepository(Categorie::class)->findOneByCatLibelle($cat) == null) {
			$categorie = new Categorie();
			$categorie->setCatLibelle($cat);
			//Associe la catégorie à l'objet :
			$objetApidae->addCategorie($categorie);
			//Ajout de lobjet à la catégorie :
			$categorie->addObjet($objetApidae);
			//$em->persist($categorie);
		} else {
			$categorie = new Categorie();
			$categorie->setCatLibelle($cat);
			//Associe la catégorie à l'objet
			$objetApidae->addCategorie($categorie);
		}
	}
}
