<?php
namespace App\Commands;

use Segrax\OpenPolicyAgent\Policy\Creator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FromOpenAPI extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('from-openapi');
        $this->setDescription('Generate a policy from an OpenAPI3 spec');
        $this->addArgument('filename', InputArgument::REQUIRED);

        //$this->addOption('auth-mode', 'am', InputOption::VALUE_OPTIONAL, 'Add checks for a token, or a specific user' )
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');

        $creator = new Creator();
        $policySet = $creator->fromFile($filename);

        var_dump($policySet->getPolicy());
        return 0;
    }

}
