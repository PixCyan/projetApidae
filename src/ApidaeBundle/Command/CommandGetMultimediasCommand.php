<?php

namespace ApidaeBundle\Command;

use ApidaeBundle\Entity\Multimedia;
use ApidaeBundle\Entity\ObjetApidae;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandGetMultimediasCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('command:getMultimedias')
            ->setDescription('Traitement des données Apidae');
    }

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
            //-- Même procédure poru chaque type d'utl

        $idsObjets = $em->getRepository(ObjetApidae::class)->getAllIds();
        foreach($idsObjets as $id) {
            //Récupération des images correspondant à l'objet actuel
            $multimedias = $em->getRepository(Multimedia::class)->getMultimediasByObjectId($id);
            foreach ($multimedias as $multimedia) {
                //-- Image originale
                $url = $multimedia->getMulUrl();
                $file = "/home/www/vhosts/swad.fr/apidae.swad.fr/web/bundles/apidae/imgApidae/originale/";
                if(is_dir($file.$id)) {
                    $this->copierImage($file, $url, $id);
                } else {
                    mkdir($file.$id);
                }


            }
        }

        $output->writeln('Command result.');
    }

    private function copierImage($path, $url, $id) {
        $nom = array_pop(explode('/',$url));
        $img = file_get_contents($url);
        file_put_contents($path.$id, $img);
    }

}
