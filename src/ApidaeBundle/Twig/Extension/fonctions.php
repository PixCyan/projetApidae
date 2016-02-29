<?php

namespace ApidaeBundle\Twig\Extension;

 class Fonctions extends  \Twig_Extension {
     private $SIT_LANGUE = 'Fr';

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
             new \Twig_SimpleFilter('typeApidae', array($this, 'getTyeApidae')));
     }

     function getLangueLib($str, $locale='') {
         if (empty ($locale)) {
             $locale = $this->SIT_LANGUE;
         }
         $debut = strpos($str, '@' . $locale . ':');
         if ($debut === false) {
             return $str;
         }
         $debut += strlen('@' . $locale . ':');
         $fin = strpos($str, '@', $debut);
         return substr($str, $debut, $fin - $debut);
     }

     function getTypeApidae($str) {
         //TODO finir
         $chaineExplode = explode("_", $str);
         return $chaineExplode[0];
     }
 }