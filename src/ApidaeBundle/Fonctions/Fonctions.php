<?php
/**
 * Created by PhpStorm.
 * User: Nadia
 * Date: 31/03/2016
 * Time: 15:07
 */
namespace ApidaeBundle\Fonctions;

class Fonctions {

    /**
     * Prend la chaine et la transforme en regex
     * @param $string
     * @return mixed|string
     */
    static function genererRegexp($string) {
        $str = trim(strtr($string, '[]|:^/$()*+?{}-\\', '               '));
        $tabLettres[] = 'aàáâãäåÀÁÂÄÅ';
        $tabLettres[] = 'eéêèëÉÊËÈ';
        $tabLettres[] = 'iìíîïÌÍÎÏ';
        $tabLettres[] = 'oòóôõöÒÓÔÕÖ';
        $tabLettres[] = 'uùúûüÙÚÛÜ';
        $tabLettres[] = 'cçÇ';
        $tabLettres[] = 'dÐ';
        $tabLettres[] = 'sšŠ';
        $tabLettres[] = 'nñÑ';
        $tabLettres[] = 'yýÿÝŸ';
        $tabLettres[] = 'zžŽ';
        foreach ($tabLettres as $val) {
            $str = preg_replace('/([' . $val . '])/ui', '(' . implode('|', Fonctions::mb_str_split($val)) . ')', $str);
        }
        return $str;
    }

    /**
     *
     * @param $str
     * @return array
     */
    static  function mb_str_split($str) {
        $result = array ();
        for ($i = 0; $i < mb_strlen($str); $i++) {
            $result[] = mb_substr($str, $i, 1);
        }
        return $result;
    }

}