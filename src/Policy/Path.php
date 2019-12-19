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

use Exception;
use Segrax\OpaPolicyGenerator\Policy\Security\Base;
use Segrax\OpaPolicyGenerator\Policy\Set;

class Path
{
    /**
     * @var array
     */
    private $pathName = [];

    /**
     * @var string
     */
    private $pathRaw = '';

    /**
     * @var array
     */
    private $pathRule = [];

    /**
     * @var string
     */
    private $method = '';

    /**
     * @var array
     */
    private $securitySchemes = [];

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var Set
     */
    private $set;

    /**
     * @var string[]
     */
    private $variables = [];

    /**
     *
     */
    public function __construct(Set $pSet, string $pPath, string $pMethod)
    {
        $this->set = $pSet;
        $this->pathRaw = $pPath;
        $this->method = strtoupper($pMethod);

        $this->pathProcess();
    }

    /**
     * @param string $pName Name of parameter
     * @param string $pLocation Location you can find (in the path, in query)
     */
    public function parameterAdd(string $pName, string $pLocation): void
    {
        $this->parameters[] = [$pName => $pLocation];
    }

    /**
     *
     */
    public function securityAdd(string $pScheme, array $pScopes): void
    {
        $this->securitySchemes[$pScheme] = $pScopes;
    }

    /**
     * Get all rules for this path
     */
    public function getRules(): string
    {
        $schemes = $this->getSecurityScehemes();
        // Public
        if (count($schemes) === 0) {
            return $this->getRule();
        }

        // Private
        $result = '';
        foreach ($schemes as $scheme => $scopes) {
            if (!is_string($scheme)) {
                throw new Exception("bad security scheme found");
            }

            $security = $this->set->securitySchemeGet($scheme);
            if (is_null($security)) {
                echo "security scheme '$scheme' could not be located\n";
                continue;
            }

            $result .= $this->getRule($security, $scopes);
        }

        return $result;
    }

    /**
     * Get all tests for this path
     */
    public function getTests(): string
    {
        $result = '';
        $schemes = $this->getSecurityScehemes();
        // Public
        if (count($schemes) === 0) {
            return $this->getTestAllow() . $this->getTestDeny();
        }

        // Private
        $result = '';
        foreach ($schemes as $scheme => $scopes) {
            if (!is_string($scheme)) {
                throw new Exception("bad security scheme '$scheme' found");
            }
            $security = $this->set->securitySchemeGet($scheme);
            if (is_null($security)) {
                echo "security scheme '$scheme' is not defined\n";
                continue;
            }

            $result .= $this->getTestAllow($scheme, $security, $scopes);
            $result .= $this->getTestDeny($scheme, $security, $scopes);
        }

        return $result;
    }

    /**
     * Get the name of this rule
     */
    protected function getName(): string
    {
        return implode('_', $this->pathName);
    }

    /**
     * Get the policy rule
     */
    protected function getRule(?Base $pSecurity = null, array $pScopes = []): string
    {
        $name = $this->set->resultNameGet();
        $result = "\n$name {\n";

        foreach ($this->getVariables($pSecurity, $pScopes) as $variable) {
            $result .= "    some {$variable}\n";
        }

        $result .= "    input.method == \"{$this->method}\"\n";
        $result .= "    input.path = " . $this->getPathRule() . "\n";
        if (!is_null($pSecurity)) {
            $result .= $pSecurity->getRule($pScopes);
        }
        $result .= "}\n";
        return $result;
    }

    /**
     * Get the policy test
     */
    protected function getTestAllow(string $pName = '', ?Base $pSecurity = null, array $pScopes = []): string
    {
        if (strlen($pName)) {
            $pName = "_$pName";
        }

        $inputs = ['path' => $this->pathName, 'method' => $this->method];

        $name = $this->set->resultNameGet();
        $result = "\ntest_" . $this->getName() . "{$pName}_allowed {\n";
        if (!is_null($pSecurity)) {
            $inputs += $pSecurity->getTestAllow($pScopes);
        }
        $result .= "    $name with input as " . json_encode($inputs) . "\n";
        $result .= "}\n";

        return $result;
    }

    /**
     * Get the policy test
     */
    protected function getTestDeny(string $pName = '', ?Base $pSecurity = null, array $pScopes = []): string
    {
        if (is_null($pSecurity)) {
            return '';
        }

        if (strlen($pName)) {
            $pName = "_$pName";
        }

        $inputs = ['path' => $this->pathName, 'method' => $this->method];

        $name = $this->set->resultNameGet();
        $result = "\ntest_" . $this->getName() . "{$pName}_denied {\n";
        $inputs += $pSecurity->getTestDeny($pScopes);
        $result .= "    not $name with input as " . json_encode($inputs) . "\n";
        $result .= "}\n";

        return $result;
    }
    /**
     * Get the path for the policy rule
     */
    protected function getPathRule(): string
    {
        return '[' . implode(',', $this->pathRule) . ']';
    }

    /**
     * Get the security for this path
     */
    protected function getSecurityScehemes(): array
    {
        return (empty($this->securitySchemes) ? $this->set->securityGlobalGet() : $this->securitySchemes);
    }

    /**
     * Get all variables
     */
    private function getVariables(?Base $pSecurity = null, array $pScopes = []): array
    {
        if (!is_null($pSecurity)) {
            return array_merge($this->variables, $pSecurity->getVariables($pScopes));
        }
        return $this->variables;
    }

    /**
     * Process our raw path and populate our name and rule
     */
    private function pathProcess(): void
    {
        if (strpos($this->pathRaw, '/') === false) {
            return;
        }
        $component = explode('/', $this->pathRaw);

        array_shift($component);
        foreach ($component as $pathPiece) {
            // is a parameter?
            if (strpos($pathPiece, '{') >= 0 && strpos($pathPiece, '}')) {
                $pathPiece = trim($pathPiece, '{}');
                $this->pathName[] = $pathPiece;
                $this->pathRule[] = $pathPiece;
                $this->variables[] = $pathPiece;
                continue;
            }

            $this->pathName[] = $pathPiece;
            $this->pathRule[] = "\"$pathPiece\"";
        }
    }
}
