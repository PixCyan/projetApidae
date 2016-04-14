<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\Categorie;
use ApidaeBundle\Entity\Evenement;
use ApidaeBundle\Entity\Langue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

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
            $jsonMenu = file_get_contents("/var/www/local/Symfony/projetApidae/tools/donneesMenu.json");
            $donneesMenu = json_decode($jsonMenu);
            $fichierMenu = fopen('/var/www/local/Symfony/projetApidae/src/ApidaeBundle/Resources/views/commun/menu.html.twig', 'w');
            $this->em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
            $this->langues = $this->em->getRepository(Langue::class)->findAll();
            $menuFinal = [];
            foreach($donneesMenu as $value) {
                $objets = null;
                if(isset($value->categories)) {
                    $objets = $this->traitementCategories($value->categories);
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
                $condition = "{% if langue.lanShortCut == '".$langue->getLanShortCut()."' %}";
                fputs($fichierMenu, $condition."\n");
                //fputs($fichierMenu, "<nav> \n \t <ul> \n");
                $ul = null;
                foreach($menuFinal as $v) {
                    if($v == $menuFinal[0]) {

                        $ul = $this->getLangueLib($v->typeObjet, $langue->getLanShortCut());
                        $liTitre = "\t <li class=\"LiMenu\">".$ul."\n";
                        fputs($fichierMenu, $liTitre."\t \t <ul> \n");

                    } else if($ul != $this->getLangueLib($v->typeObjet, $langue->getLanShortCut())) {

                        $ul = $this->getLangueLib($v->typeObjet, $langue->getLanShortCut());
                        $liTitre = "\t <li class=\"LiMenu\">".$ul."\n";
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

    private function traitementCategories($categories) {
        $objets = $this->em->getRepository(Categorie::class)->findOneByCatId($categories[0]->id);
        //var_dump($categories);
        if(!$objets && count($categories) > 1) {
            $i = 1;
            while($i < count($categories)-1  && !$objets) {
                echo $i."\n";
                $t = count($categories)-1;
                echo $t."\n";
                //se stop si $objets != null et retourn $objets
                $objets = $this->em->getRepository(Categorie::class)->findOneByCatId($categories[$i]->id);
                $i++;
            }
        }
        return $objets;
    }

    private function traitementChaine($v, $langue) {

        if(isset($v->categories)) {
            $liDebut = "\t \t \t<li><a href=\"{{ path('liste', {'typeObjet': '".$this->getLangueLib($v->typeObjet, $langue->getLanShortCut() )
                ."', 'categorieId': '".$v->categories[0]->id."'}) }}\">";
        } else {
            $liDebut = "\t \t \t<li><a href=\"{{ path('listeEvenement', {'typeObjet': '".$this->getLangueLib($v->typeObjet, $langue->getLanShortCut() )
                ."', 'periode': '".$v->periode."'}) }}\">";
        }
        $liFin = $this->getLangueLib($v->libelle, $langue->getLanShortCut()) ."</a></li>";
        return $liDebut.$liFin;
    }

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