<?php

/*
Copyright (c) 2019 Robert Crossfield

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/**
 * @see       https://github.com/segrax/opa-policy-generator
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace Segrax\OpaPolicyGenerator\App\Commands;

use Segrax\OpaPolicyGenerator\Policy\Creator;
use Segrax\OpaPolicyGenerator\Policy\Testing;
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

        //$this->addOption('auth-mode', 'am', InputOption::VALUE_OPTIONAL,
        $this->addOption('output', '', InputArgument::OPTIONAL);

        //'Add checks for a token, or a specific user' )
    }

    /**
     *  @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        if (!is_string($filename)) {
            $output->writeln('Invalid input filename');
            return -1;
        }

        $creator = new Creator();
        $policySet = $creator->fromFile($filename);
        if (is_null($policySet)) {
            $output->writeln("Failed");
            return -1;
        }
        $saveas = 'output';

        if ($input->hasOption('output')) {
            $saveas = $input->getOption('output');
            if (!is_string($saveas)) {
                $output->writeln('Invalid output name');
                return -1;
            }
        }

        $policy = $saveas . '.rego';
        $policyTest = $saveas . '_test.rego';

        $policies = $policySet->policiesGet();

        file_put_contents($policy, $policies['policy']);
        file_put_contents($policyTest, $policies['test']);

        $tester = new Testing();
        echo $tester->test($policy, $policyTest);

        return 0;
    }
}
