<?php

namespace ApidaeBundle\Twig\Extension;

 use ApidaeBundle\Entity\Langue;
 use ApidaeBundle\Entity\ObjetApidae;
 use Doctrine\ORM\EntityManager;
 use Twig_Extension;
 use Twig_SimpleFunction;

 class Fonctions extends Twig_Extension {
     private $SIT_LANGUE = 'Fr';

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
             new \Twig_SimpleFunction('langueLibelle', array($this, 'getLangueLibelle'))
             );
     }

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

     function getTypeApidae($str) {
         $chaineExplode = explode("_", $str);
         return $chaineExplode[0];
     }

     function getTradLangue(ObjetApidae $objet, Langue $langue) {
         foreach($objet->getTraductions() as $value) {
             if($value->getLangue() == $langue) {
                 return $value;
             }
         }
         return null;
     }

     function getLangueLibelle($langue) {
         //$em = $this->container->get('doctrine')->getManager();
         $l = $this->em->getRepository('ApidaeBundle:Langue')->findOneBy(['lanShortCut' => ucwords($langue)]);
         return $l->getLanLibelle();
         //return "test";
     }
 }