<?php
namespace ApidaeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * La commande RecuperationDonneesApidaeCommand recupere l'export prepare par la plateforme Apidae. Decompresse le fichier.
 * Informe du succes ou non du traitement par email.
 *
 * Class RecuperationDonneesApidaeCommand
 * @package ApidaeBundle\Command
 */
class RecuperationDonneesApidaeCommand extends ContainerAwareCommand {

    /**
     * Configuration de la commande
     */
    protected function configure()
    {
        $this
            ->setName('command:recuperationDonnees')
            ->setDescription('Récupération des dichiers de données d\'Apidae');
    }

    /**
     * Execution de la commande
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO récupération URL
        // http://export.sitra-tourisme.com/exports/1464_20160506-0958_NPQ1jG.zip
        $logs = fopen('logRecuperationDonneesApidae.txt', 'w');
        if ($tab = $_POST) {
            foreach($tab as $key => $value) {
                fputs($logs, $key." : ".$value."\n");
            }
        }
        fclose($logs);
        if(isset($_POST['statut']) && !is_null($_POST['statut'])) {
            if($_POST['statut'] == "SUCCESS") {
                //TODO lancer la commande
            } elseif($_POST['statut'] == "ERROR") {
                //envoi d'un email
                $this->notificationMail();
            }
        }
    }

    /**
     * Notifie par email, informe de l'etat du processus (succes/error)
     */
    protected function notificationMail() {
        $message = \Swift_Message::newInstance()
            ->setSubject('[ERROR] Récupération du fichier de données Apidae')
            ->setFrom('send@example.com')
            ->setTo('nadiaraffenne@gmail.com')
            ->setContentType('text/html')
            ->setBody(
                $this->getContainer()->get('templating')->render(
                    'ApidaeBundle:Emails/recuperationDonnees.html.twig'
                ));
        $this->getContainer()->get('mailer')->send($message);
    }
}