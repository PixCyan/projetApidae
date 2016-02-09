<?php

namespace ApidaeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Traitement extends ContainerAwareCommand {
    // …
    protected function configure() {
        $this
            ->setName('command:traitement')
            ->setDescription('Traitement des données Apidae');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("Test !");
    }
}
