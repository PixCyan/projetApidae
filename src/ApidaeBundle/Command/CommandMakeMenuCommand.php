<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Langue;
use ApidaeBundle\Entity\SelectionApidae;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

/**
 * Commande chargée de générer le menu d'après le fichier 'donneesMenu.json'
 * Class CommandMakeMenuCommand
 * @package ApidaeBundle\Command
 */
class CommandMakeMenuCommand extends ContainerAwareCommand
{
    private $em;
    private $SITE_LANGUE = "Fr";
    private $langues;

    protected function configure() {
        $this
            ->setName('command:makeMenu')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $jsonMenu = file_get_contents("/var/www/html/sites/projetApidae/tools/donneesMenu.json");
            $donneesMenu = json_decode($jsonMenu);
            $fichierMenu = fopen('/var/www/html/sites/projetApidae/src/ApidaeBundle/Resources/views/commun/menu.html.twig', 'w');
            $this->em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
            $this->langues = $this->em->getRepository(Langue::class)->findAll();
            $menuFinal = [];
            foreach($donneesMenu as $value) {
                $objets = null;
                if(isset($value->selectionApidae)) {
                    $objets = $this->traitementSelection($value->selectionApidae);
                } elseif(isset($value->periode)) {
                    if($value->periode == 1) {
                        $objets = $this->em->getRepository(Evenement::class)->getAujourdhui2();
                    } else {
                        $objets = $this->em->getRepository(Evenement::class)->getInterval($value->periode);
                    }
                }
                if($objets) {
                    $menuFinal[] = $value;
                }
            }

            foreach($this->langues as $langue) {
                $condition = "{% if app.request.locale == '".strtolower($langue->getLanShortCut())."' %}";
                fputs($fichierMenu, $condition."\n");
                //fputs($fichierMenu, "<nav> \n \t <ul> \n");
                $ul = null;
                foreach($menuFinal as $v) {
                    if($v == $menuFinal[0]) {
                        $ul = $this->getLangueLib($v->typeObjet, $langue->getLanShortCut());
                        $liTitre = "\t <li><a>".$ul."</a>\n";
                        fputs($fichierMenu, $liTitre."\t \t <ul> \n");
                    } else if($ul != $this->getLangueLib($v->typeObjet, $langue->getLanShortCut())) {
                        $ul = $this->getLangueLib($v->typeObjet, $langue->getLanShortCut());
                        $liTitre = "\t <li><a>".$ul."</a>\n";
                        fputs($fichierMenu, "\t \t </ul> \n \t </li> \n".$liTitre);
                        fputs($fichierMenu, "\t \t <ul> \n");
                    }
                    $chaineLi = $this->traitementChaine($v, $langue);

                    fputs($fichierMenu, $chaineLi);
                    fputs($fichierMenu, "\n");
                }
                fputs($fichierMenu, "\t \t </ul>\n \t </li> \n");
                //fputs($fichierMenu, "</nav> \n");
                fputs($fichierMenu, "{% endif %} \n");
            }

            fclose($fichierMenu);

            //---
            $output->writeln("Création du menu : ok.");
        } catch (Exception $e) {
            $output->writeln("Problème : " . $e->getMessage());
        }
    }

    private function traitementSelection($categories) {
        $objets = null;
        $s = $this->em->getRepository(SelectionApidae::class)->findOneByIdSelectionApidae($categories[0]->id);
        if($s) {
            $objets = $s->getObjets();
        } else if(!$objets && count($categories) > 1) {
            $i = 1;
            while($i < count($categories)-1  && !$objets) {
                echo $i."\n";
                $t = count($categories)-1;
                echo $t."\n";
                //se stop si $objets != null et retourn $objets
                $s = $this->em->getRepository(SelectionApidae::class)->findOneByIdSelectionApidae($categories[$i]->id);
                $objets = $s->getObjets();
                $i++;
            }
        }
        return $objets;
    }

    /**
     * Renvoie une chaine qui génère les '<li>' contenant les liens '<a>' du menu
     * @param $v  l'objet que l'ont traite
     * @param $langue  la langue que l'ont traite
     * @return string
     */
    private function traitementChaine($v, $langue) {
        if(isset($v->selectionApidae)) {
            $liDebut = "\t \t \t<li><a href=\"{{ path('liste', {'typeObjet': '".
                $this->traitementChaineUrl($this->getLangueLib($v->typeObjet, $langue->getLanShortCut() ))
                ."', 'categorieId': '".$v->selectionApidae[0]->id."', 'libelleCategorie': '".
                $this->traitementChaineUrl($this->getLangueLib($v->libelle, $langue->getLanShortCut()))."'}) }}\">";
        } else {
            $liDebut = "\t \t \t<li><a href=\"{{ path('listeEvenement', {'typeObjet': '".
                $this->traitementChaineUrl($this->getLangueLib($v->typeObjet, $langue->getLanShortCut()))
                ."', 'periode': '".$v->periode."', 'libelleCategorie': '".
                $this->traitementChaineUrl($this->getLangueLib($v->libelle, $langue->getLanShortCut()))."'}) }}\">";
        }
        $liFin = $this->getLangueLib($v->libelle, $langue->getLanShortCut()) ."</a></li>";
        return $liDebut.$liFin;
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
     * Renvoie la chaine correspondant à la langue donnée
     * @param $str
     * @param string $locale
     * @return string
     */
    function getLangueLib($str, $locale='') {
        if (empty ($locale)) {
            $locale = $this->SITE_LANGUE;
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
