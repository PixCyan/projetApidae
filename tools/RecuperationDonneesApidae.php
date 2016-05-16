<?php
/**
 * Created by PhpStorm.
 * User: Nadia
 * Date: 25/02/2016
 * Time: 16:28
 */


$logs = fopen('logRecuperationDonneesApidae.txt', 'r+');

if ($tab = $_POST) {
    foreach($tab as $key => $value) {
        fputs($logs, $key." : ".$value."\n");
    }
}
if(isset($_POST['statut']) && !is_null($_POST['statut'])) {
    if($_POST['statut'] == "SUCCESS") {
        //lancement de la commande
    } elseif($_POST['statut'] == "ERROR") {
        //envoi d'un email
    }
}

fclose($logs);
