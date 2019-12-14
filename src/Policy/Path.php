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
 * @see       https://github.com/segrax/OpaPolicyGenerator
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace Segrax\OpaPolicyGenerator\Policy;

use Segrax\OpaPolicyGenerator\Policy\Security\Base;

class Path
{
    private $pathName;
    private $pathRaw;
    private $pathRule;
    private $method;

    private $security;
    private $parameters;

    private $set;

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
     * @param $pName Name of parameter
     * @param $pLocation Location you can find (in the path, in query)
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
        $this->security[$pScheme] = $pScopes;
    }

    /**
     * Get all rules for this path
     */
    public function getRules(): string
    {
        $security = $this->getSecurity();

        // Public
        if (count($security) === 0) {
            return $this->getRule();
        }

        // Private
        $result = '';
        foreach ($security as $scheme => $scopes) {
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
        $security = $this->getSecurity();

        return $result;
    }

    /**
     * Get the name of this rule
     */
    public function getName(): string
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
        $result .= "    input.method == \"{$this->method}\"\n";
        $result .= "    input.path = " . $this->getPathRule() . "\n";
        if (!is_null($pSecurity)) {
            $result .= $pSecurity->getRule($pScopes);
        }
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
    protected function getSecurity()
    {
        return (empty($this->security) ? $this->set->securityGlobalGet() : $this->security);
    }

    /**
     * Process our raw path and populate our name and rule
     */
    private function pathProcess()
    {
        $component = explode('/', $this->pathRaw);
        if (empty($component)) {
            return;
        }

        array_shift($component);
        foreach ($component as $pathPiece) {
            // is a parameter?
            if (strpos($pathPiece, '{') >= 0 && strpos($pathPiece, '}')) {
                $pathPiece = trim($pathPiece, '{}');
                $this->pathName[] = $pathPiece;
                $this->pathRule[] = $pathPiece;
                continue;
            }

            $this->pathName[] = $pathPiece;
            $this->pathRule[] = "\"$pathPiece\"";
        }
    }
}
