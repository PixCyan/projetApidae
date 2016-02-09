<?php

namespace ApidaeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class Traitement extends ContainerAwareCommand {
    // …
    protected function configure() {
        $this
            ->setName('crontask:traitement')
            ->setDescription('Traitement des données Apidae');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writln("Test !");
    }
}
