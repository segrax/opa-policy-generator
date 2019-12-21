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

use RuntimeException;
use Segrax\OpaPolicyGenerator\Convert\Json;
use Segrax\OpaPolicyGenerator\Convert\Yaml;
use Segrax\OpaPolicyGenerator\Policy\Set;

/**
 * Create policies from other formats
 */
class Creator
{
    /**
     * Generate a policy and tests from a file
     */
    public function fromFile(string $pFilename): ?Set
    {
        $converter = null;

        $ext = pathinfo($pFilename, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'yaml':
            case 'yml':
                $converter = new Yaml();
                break;

            case 'json':
                $converter = new Json();
                break;

            default:
                break;
        }

        if (is_null($converter)) {
            throw new RuntimeException("Unknown Filetype: $pFilename");
        }

        $content = file_get_contents($pFilename);
        if ($content === false) {
            return null;
        }
        return $converter->fromString($content);
    }

    /**
     * Export a policy and tests into files
     */
    public function toFile(Set $pPolicySet, string $pFilenameBase): array
    {
        $policy = $pFilenameBase . '.rego';
        $policyTest = $pFilenameBase . '_test.rego';

        $policies = $pPolicySet->policiesGet();

        file_put_contents($policy, $policies['policy']);
        file_put_contents($policyTest, $policies['test']);

        return ['policy' => $policy, 'test' => $policyTest];
    }

    /**
     * Test a policy set via 'opa test'
     */
    public function testSet(Set $pPolicySet): string
    {
        $saveas = sys_get_temp_dir() . '/' . uniqid();
        $files = $this->toFile($pPolicySet, $saveas);

        $tester = new Testing();
        $result = $tester->test($files['policy'], $files['test']);

        unlink($files['policy']);
        unlink($files['test']);

        return $result;
    }
}
