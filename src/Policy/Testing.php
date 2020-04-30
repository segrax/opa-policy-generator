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

namespace Segrax\OpaPolicyGenerator\Policy;

/**
 * Interface to OPA test
 */
class Testing
{
    private const CONTAINER_OPA_BINARY = '/opa';

    /**
     * @var string
     */
    private $binary;

    /**
     * Setup class
     */
    public function __construct(string $pOpaBinary = self::CONTAINER_OPA_BINARY)
    {
        $this->binary = $pOpaBinary;
    }

    /**
     * Get the policy coverage report
     */
    public function coverage(string $pPolicy, string $pTest): string
    {
        $reports = json_decode($this->execute($pPolicy, $pTest, true), true);
        return json_encode($reports['files'][$pPolicy]);
    }

    /**
     *
     */
    public function test(string $pPolicy, string $pTest): string
    {
        return $this->execute($pPolicy, $pTest, false);
    }

    
    /**
     * 
     */
    protected function runOPA(string $pCmd, string &$pStdOut, string &$pStdError) {
        $cmd = $this->binary . " test $pCmd";
        $proc = proc_open($cmd,[ 1 => ['pipe','w'], 2 => ['pipe','w'],],$pipes);
        $pStdOut = stream_get_contents($pipes[1]);
        $pStdError = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return proc_close($proc);
    }

    /**
     * Run a policy against its set of tests
     */
    protected function execute(string $pPolicy, string $pTest, bool $pCoverage): string
    {
        $cmd = escapeshellarg($pPolicy) . ' ';
        $cmd .= escapeshellarg($pTest) . ' ';

        switch($pCoverage) {
            case false:
                $cmd .= ' -l';  // Show line numbers
            break;
            case true:
                $cmd .= ' --coverage --format=json';
            break;
        }

        $results = [];
        $errors = [];
        $code = $this->runOPA($cmd, $results, $errors);
        return $code == 1 ? $errors : $results;
    }
}
