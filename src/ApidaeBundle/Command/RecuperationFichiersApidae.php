<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecuperationDonneesApidaeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('command:recuperationDonnees')
            ->setDescription('Récupération des dichiers de données d\'Apidae');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
                $this->notificationMail();
            }
        }
        fclose($logs);
    }

    private function notificationMail() {
        $message = \Swift_Message::newInstance()
            ->setSubject('[ERROR] Récupération du fichier de données Apidae')
            ->setFrom('send@example.com')
            ->setTo('nadiaraffenne@gmail.com')
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/
                    'Emails/recuperationDonnees.html.twig'
                ),
                'text/html'
            )
        ;
        $this->getContainer()->get('mailer')->send($message);

        //return $this->render(...);
    }
}