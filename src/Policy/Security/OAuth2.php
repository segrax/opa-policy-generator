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

namespace Segrax\OpaPolicyGenerator\Policy\Security;

class OAuth2 extends Base
{
    protected $flows = [];

    public function flowAdd(string $pType, array $pScopes)
    {
        $this->flows[$pType] = $pScopes;
    }

    /**
     *
     */
    public function getRule(array $pScopes): string
    {
        $result = '';
        foreach ($pScopes as $count => $scope) {
            $result .= "    input.token.scopes[scope{$count}] == \"$scope\"\n";
        }

        return $result;
    }

    public function getTest(array $pScopes): array
    {
        $result = ['token' => ['scopes' => []]];

        foreach ($pScopes as $count => $scope) {
            $result['token']['scopes'][] = $scope;
        }
        return $result;
    }
}
