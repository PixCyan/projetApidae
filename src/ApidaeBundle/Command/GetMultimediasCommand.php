<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\Multimedia;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * La commande GetMultimediasCommand gere la recuperation de tous les multimedias et les enregistre en local
 *
 * Class GetMultimediasCommand
 * @package ApidaeBundle\Command
 */
class GetMultimediasCommand extends ContainerAwareCommand {

    /**
     * Configuration de la commande
     */
    protected function configure()
    {
        $this
            ->setName('command:getMultimedias')
            ->setDescription('Traitement des données Apidae');
    }

    /**
     * Execution de la commande
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
        //-- Récupération des ids de tous les objets
        //-- Pour chaque id --> récupérer les urls multimedias correspondantes
        //-- Pour chaque multimedias :
            //--> url fiche
                //-- Si le dossier d'id i n'existe pas le créer
                    //--récupérer l'image à l'url donnée d'après son nom
                    //-- si l'image du même nom n'existe pas déjà
                        //-- l'enregistrer dans le dossier d'id i
            //-- Même procédure pour chaque type d'utl

        $logs = fopen('logImages.txt', 'w');

        $idsObjets = $em->getRepository(ObjetApidae::class)->getAllIds();
        foreach($idsObjets as $ids) {
            fputs($logs, $ids['idObj']."\n");
            //Récupération des images correspondant à l'objet actuel
            $multimedias = $em->getRepository(Multimedia::class)->getMultimediasByObjectId($ids['id']);
            foreach ($multimedias as $multimedia) {
                //-- Image originale
                    $url = $multimedia->getMulUrl();
                    $this->traitementImage($url, $ids);

                   //-- Image fiche
                   $url = $multimedia->getMulUrlFiche();
                   $this->traitementImage($url, $ids);

                   //-- Image diaporama
                   $url = $multimedia->getMulUrlDiapo();
                   $this->traitementImage($url, $ids);
                }
            //$output->writeln($ids['idObj']."\n");
        }
        fclose($logs);

        $output->writeln('Command result.');
    }

    /**
     * Traite le chemin de destination de l'image
     * @param $url
     * @param $ids
     */
    protected function traitementImage($url,$ids){
        $file = "/home/www/vhosts/swad.fr/apidae.swad.fr/src/ApidaeBundle/Resources/public/imgApidae/";
        //$file = "/var/www/local/Symfony/projetApidae/web/bundles/apidae/imgApidae/";
        $array = explode('/',$url);
        $name = array_pop($array);
        $path = $file.$ids['idObj']."/";

        if(file_exists($file.$ids['idObj']) && !file_exists($path.$name)) {
            $this->copierImage($path.$name, $url);
        } else if(!file_exists($file.$ids['idObj'])) {
            mkdir($file . $ids['idObj']);
            $this->copierImage($path.$name, $url);
        }
    }

    /**
     * Recupere et Enregistre l'image dans le dossier
     * @param $path
     * @param $url
     */
    protected function copierImage($path, $url) {
        if($this->urlExists($url)) {
            //$img = file_get_contents($url);
            //$img = fopen($url, "r");
            //file_put_contents($path, $img);
            if(!copy($url, $path)) {
                print("Erreur lors de la copie");
                die();
            }
        }
    }

    /**
     * Verifie si l'url est bonne
     * @param $url
     * @return bool
     */
    protected function urlExists($url){
        $headers=get_headers($url);
        return stripos($headers[0],"200 OK")?true:false;
    }

}
