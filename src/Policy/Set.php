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

use Segrax\OpaPolicyGenerator\Policy\Security\Base;

/**
 * A policy and its tests
 */
class Set
{
    /**
     * Name of the primary result
     */
    private const DEFAULT_RESULT_NAME = 'allow';

    /**
     * Default value of the primary result
     */
    private const DEFAULT_RESULT = false;

    /**
     * @var Path[] All paths
     */
    public $paths = [];

    /**
     * @var string Name of the package
     */
    private $packageName = '';

    /**
     * @var string Name of the result
     */
    private $resultName = self::DEFAULT_RESULT_NAME;

    /**
     * @var bool Default result value
     */
    private $resultDefault = self::DEFAULT_RESULT;

    /**
     * @var Base[] All security schemes
     */
    private $security = [];

    /**
     * @var array global security rules
     */
    private $securityGlobal = [];

    /**
     * Setup the policy set
     */
    public function __construct(
        string $pPackageName,
        string $pDefaultName = self::DEFAULT_RESULT_NAME,
        bool $pDefaultResult = self::DEFAULT_RESULT
    ) {
        $this->packageName = $pPackageName;
        $this->resultName = $pDefaultName;
        $this->resultDefault = $pDefaultResult;
    }

    /**
     * Add a path
     */
    public function pathAdd(Path $pPath): void
    {
        $this->paths[] = $pPath;
    }

    /**
     * Add a security scheme
     */
    public function securitySchemeAdd(Base $pSecurity): void
    {
        $this->security[$pSecurity->getSchemeName()] = $pSecurity;
    }

    /**
     * Get a security scheme
     */
    public function securitySchemeGet(string $pName): ?Base
    {
        foreach ($this->security as $name => $security) {
            if (strtolower($name) === strtolower($pName)) {
                return $security;
            }
        }
        return null;
    }

    /**
     * Add a global security scheme
     */
    public function securityGlobalAdd(string $pType, array $pOptions): void
    {
        $this->securityGlobal[$pType] = $pOptions;
    }

    /**
     * Get the global security schemes
     */
    public function securityGlobalGet(): array
    {
        return $this->securityGlobal;
    }

    /**
     * Get the policy and its test
     */
    public function policiesGet(): array
    {
        return ['policy' => $this->policyCreate(),
                'test' => $this->policyTestCreate()];
    }

    /**
     * Get the name of the result
     */
    public function resultNameGet(): string
    {
        return $this->resultName;
    }

    /**
     * Get the default value for the result
     */
    public function resultDefaultGet(): bool
    {
        return $this->resultDefault;
    }

    /**
     * Get the policy
     */
    private function policyCreate(): string
    {
        $result = "package {$this->packageName}\n\n";
        $result .= "default {$this->resultName} = " . (($this->resultDefault === false) ? 'false' : 'true') . "\n";
        foreach ($this->paths as $path) {
            $result .= $path->getRules();
        }
        return $result;
    }

    /**
     * Get the test policy
     */
    private function policyTestCreate(): string
    {
        $result = "package {$this->packageName}\n";
        foreach ($this->paths as $path) {
            $result .= $path->getTests();
        }
        return $result;
    }
}
