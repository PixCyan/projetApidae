<?php

namespace ApidaeBundle\Twig\Extension;

 use ApidaeBundle\Entity\Langue;
 use ApidaeBundle\Entity\ObjetApidae;
 use Doctrine\ORM\EntityManager;
 use Twig_Extension;
 use Twig_SimpleFunction;

 class Fonctions extends Twig_Extension {
     private $SIT_LANGUE = 'Fr';
     private $PATH = "http://apidae.swad.fr/web/bundles/apidae/imgApidae/";

     /** @var EntityManager $em  */
     protected $em;
     /**
      * Extension constructor
      *
      * @param EntityManager $em
      * @return Fonctions
      */
     public function __construct(EntityManager $em) {
         $this->em = $em;
     }

     /**
      * Returns the name of the extension.
      *
      * @return string The extension name
      */
     public function getName()
     {
         return 'fonctions_extension';
     }

     public function getFilters() {
         return array(new \Twig_SimpleFilter('langueLib', array($this, 'getLangueLib')),
             new \Twig_SimpleFilter('typeApidae', array($this, 'getTypeApidae'))
             );
     }

     public function getFunctions() {
         return array(new Twig_SimpleFunction('tradLangue', array($this, 'getTradLangue')),
             new \Twig_SimpleFunction('langueLibelle', array($this, 'getLangueLibelle')),
             new \Twig_SimpleFunction('pathMultimedia', array($this, 'getPathMultimedia')),
             new \Twig_SimpleFunction('nextImgPath', array($this, 'getNextImgPath'))
             );
     }

     /**
      * Renvoie la chaine de traduction corresondant à la langue (locale) données pour une chaine donnée
      * Pour une chaine de type : @Fr:foo@En:bar
      * @param $str
      * @param string $locale
      * @return string
      */
     function getLangueLib($str, $locale='') {
         if (empty ($locale)) {
             $locale = $this->SIT_LANGUE;
         }
         if($str === '@' || $str === null) {
             $res = '';
         }

         $debut = strpos($str, '@' . $locale . ':');
         if ($debut === false) {
             $locale = $this->SIT_LANGUE;
             $debut = strpos($str, '@' . $locale . ':');
             if($debut === false) {
                 return '';
             }
             //return $str;
         }
         $debut += strlen('@' . $locale . ':');
         $fin = strpos($str, '@', $debut);
         $res = substr($str, $debut, $fin - $debut);

         return $res;
     }

     /**
      * Renvoie la traduction correspondant à un objet et une langue donnés.
      * @param ObjetApidae $objet
      * @param Langue $langue
      * @return null
      */
     function getTradLangue(ObjetApidae $objet, Langue $langue) {
         /*foreach($objet->getTraductions() as $value) {
             if($value->getLangue() == $langue) {
                 return $value;
             }
         }*/
         $l = $this->em->getRepository('ApidaeBundle:TraductionObjetApidae')->findOneBy([
             'langue' => $langue,
             'objet' => $objet]);
         if($l) {
             return $l;
         } else {
             return null;
         }
     }

     /**
      * Renvoie le libelle de la langue correspondant au raccourci donnée (Fr => Français)
      * @param $langue
      * @return string
      */
     function getLangueLibelle($langue) {
         //$em = $this->container->get('doctrine')->getManager();
         $l = $this->em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
         return $l->getLanLibelle();
         //return "test";
     }

     /**
      * Renvoie le première élément d'un type Apidae
      * @param $str
      * @return mixed
      */
     function getTypeApidae($str) {
         $chaineExplode = explode("_", $str);
         return $chaineExplode[0];
     }

     /**
      * Renvoie le path du multimedia
      * @param $url
      * @param $idObj
      * @return string
      */
     function getPathMultimedia($url, $idObj) {
         //Traitement de la châine PATH
         $array = explode('/', $url);
         $name = array_pop($array);
         $path = $this->PATH.$idObj."/".$name;

         return $path;
     }

     /**
      * Parcour la liste des mutlimedias et renvoi le premier qui possède un path diaporama valide
      * @param $multimedias
      * @return null
      */
     function getNextImgPath($multimedias) {
         $res = null;
         foreach ($multimedias as $m) {
             if($m->getMulUrlDiapo()) {
                 $res = $m->getMulUrlDiapo();
                 break;
             }
         }
        return $res;

     }
 }